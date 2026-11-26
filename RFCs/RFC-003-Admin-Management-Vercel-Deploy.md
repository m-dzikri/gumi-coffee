# RFC-003: Admin Order & Menu Management and Vercel Serverless Configuration

**Features**: F10, F11, F12, F13
**Depends on**: RFC-002
**Rules**: ARCH-1, DATA-1, DATA-3, SEC-1, SEC-3, UI-1, UI-2, TEST-1, OPS-1
**Complexity**: Medium

## 1. Summary

RFC ini menyelesaikan sisi operasional Admin serta kesiapan deployment serverless di Vercel:
- **Dashboard & Manajemen Pesanan Admin (`/admin/orders`)**: Menampilkan daftar pesanan masuk dengan filter berdasarkan `status` dan pencarian `order_code` secara *case-insensitive* maupun parsial (`0042` atau `gm-0042`), aksi penandaan lunas untuk pesanan `cash`, aksi perubahan status pesanan secara berurutan (`pending -> preparing -> ready -> completed`), serta aksi pembatalan pesanan `unpaid` (termasuk pesanan `cash` berstatus `ready + unpaid` yang tidak diambil pelanggan) menggunakan *state machine* terpusat dari `RFC-002`.
- **Manajemen Katalog Menu Admin (`/admin/menus`)**: CRUD data menu dengan pembuatan `slug` unik otomatis dari `name`, validasi `price >= 1000`, tombol *quick-toggle* `is_available`, dan penghapusan menu yang aman terhadap riwayat `order_items`.
- **Konfigurasi Runtime Serverless Vercel (`vercel.json` & `api/index.php`)**: Pengalihan path tulis runtime Laravel ke `/tmp` agar bebas dari error *read-only filesystem* di Vercel.

---

## 2. Technical Approach & Architecture

### 2.1 Admin Order Dashboard, Filtering & Search (`F10`)
- Seluruh rute admin berada di bawah prefix `/admin` dengan middleware `['web', 'auth', 'admin']` (`SEC-1`). Rute `GET /admin` (`admin.dashboard` dari `RFC-002`) mengarahkan langsung ke `route('admin.orders.index')` (`/admin/orders`).
- **Query Filter & Pencarian pada `GET /admin/orders`**:
  - **Filter Status (`?status=`)**: Jika parameter `status` bernilai salah satu dari `['pending', 'preparing', 'ready', 'completed', 'cancelled']`, filter query dengan `where('status', $status)`.
  - **Pencarian Kode Pesanan (`?search=`)**: Jika parameter `search` tidak kosong, normalisasi string dengan `trim()` dan lakukan pencarian *case-insensitive* parsial pada `order_code`:
    ```php
    $query->whereRaw('LOWER(order_code) LIKE ?', ['%' . strtolower(trim($search)) . '%']);
    ```
    Dengan demikian, pencarian `0042`, `gm-0042`, atau `GM-0042` semuanya menemukan pesanan `GM-0042`.

### 2.2 Admin Payment & Status Mutations via Centralized State Machine (`F10`, `F11`)
Controller Admin **tidak** menulis logika `if/switch` syarat transisi sendiri, melainkan memanggil metode terpusat pada model `Order` yang telah didefinisikan di `RFC-002` (`ARCH-1`, `DATA-1`):
1. **Tandai Lunas Pesanan Tunai (`POST /admin/orders/{order}/mark-paid`)**:
   - Memanggil `$order->markCashPaidByAdmin()`.
   - Di dalam `DB::transaction()` + `lockForUpdate()`, hanya mengizinkan jika `payment_method === 'cash'`, `payment_status === 'unpaid'`, dan `status` berada pada `['pending', 'preparing', 'ready']`.
   - Mengubah `payment_status = 'paid'` dan `paid_at = now()`.
   - Menolak dengan `ValidationException::withMessages(['payment_status' => ...])` jika `payment_method !== 'cash'`, `payment_status === 'paid'`, atau `status` adalah `'cancelled'` / `'completed'`.
2. **Transisi Status & Pembatalan oleh Admin (`PATCH /admin/orders/{order}/status`)**:
   - Validasi input form: `status` wajib salah satu dari `['preparing', 'ready', 'completed', 'cancelled']`.
   - Memanggil `$order->transitionStatusByAdmin($validated['status'])`.
   - Di dalam `DB::transaction()` + `lockForUpdate()`, menerapkan matriks aturan:
     - `pending -> preparing`: Diizinkan jika `payment_method === 'cash'` (baik `unpaid` maupun `paid`), ATAU jika `payment_method === 'qris_dummy'` **dan** `payment_status === 'paid'`. Ditolak jika `qris_dummy` masih `unpaid` atau status asal bukan `pending`.
     - `preparing -> ready`: Hanya diizinkan dari status `preparing`.
     - `ready -> completed`: Hanya diizinkan dari status `ready` **dan** wajib `payment_status === 'paid'`. Ditolak jika masih `unpaid`.
     - `-> cancelled`: Hanya diizinkan jika `payment_status === 'unpaid'` **dan** status asal berada di `['pending', 'preparing', 'ready']` (mengatasi *deadlock* pesanan `cash` yang sudah `ready` namun tidak diambil pelanggan). Pesanan yang sudah `paid` selalu ditolak untuk dibatalkan.

### 2.3 Admin Menu Management (CRUD & Quick-Toggle) (`F12`)
- **Validasi Input Menu (`POST /admin/menus` & `PUT /admin/menus/{menu}`) (`SEC-3`)**:
  - `name`: `required|string|max:255`
  - `category`: `required|string` dan wajib termasuk dalam `config('gumi.categories')`
  - `description`: `nullable|string|max:1000`
  - `price`: `required|integer|min:1000`
  - `image_path`: `nullable|string|max:2048` (mendukung path aset statis di bawah `public/`, URL gambar eksternal, atau upload file opsional melalui `Storage`)
  - `is_available`: `sometimes|boolean`
- **Pembuatan `slug` Unik Otomatis**:
  - Dibuat dari `Str::slug($name)`. Jika slug sudah digunakan oleh baris `menus` lain, tambahkan sufiks numerik bertahap (`-2`, `-3`, dst.) hingga unik sebelum disimpan ke database.
- **Quick-Toggle Ketersediaan (`PATCH /admin/menus/{menu}/toggle-availability`)**:
  - Membalik nilai boolean `$menu->update(['is_available' => !$menu->is_available])`.
- **Penghapusan Menu (`DELETE /admin/menus/{menu}`)**:
  - Menghapus baris menu dari tabel `menus`. Karena `order_items.menu_id` menggunakan `nullOnDelete()` dan `order_items` menyimpan salinan `menu_name`, `unit_price`, dan `subtotal`, riwayat pesanan yang memuat menu tersebut tetap utuh (`DATA-3`).

### 2.4 Vercel Serverless Runtime Configuration (`F13`, `OPS-1`)
Untuk menjalankan aplikasi di lingkungan *read-only filesystem* Vercel:
1. **`api/index.php`**:
   - Menyiapkan subdirektori sementara di `/tmp` jika belum ada (`/tmp/views`, `/tmp/cache`, `/tmp/storage/framework/views`, `/tmp/storage/framework/cache`).
   - Memanggil `public/index.php`.
2. **`vercel.json`**:
   - Mengonfigurasi runtime PHP serverless (`vercel-php`) untuk `api/index.php`, menyajikan aset statis dari `public/`, dan menetapkan variabel lingkungan runtime:
     - `APP_CONFIG_CACHE=/tmp/cache/config.php`
     - `APP_ROUTES_CACHE=/tmp/cache/routes.php`
     - `APP_SERVICES_CACHE=/tmp/cache/services.php`
     - `APP_PACKAGES_CACHE=/tmp/cache/packages.php`
     - `VIEW_COMPILED_PATH=/tmp/views`
     - `LOG_CHANNEL=stderr`
     - `SESSION_DRIVER=cookie`

---

## 3. Routes & Interfaces Exposed

| Method | URI | Route Name | Middleware | Keterangan |
|---|---|---|---|---|
| `GET` | `/admin/orders` | `admin.orders.index` | `web`, `auth`, `admin` | Daftar pesanan masuk dengan filter `status` & pencarian `search` (`order_code`) |
| `GET` | `/admin/orders/{order}` | `admin.orders.show` | `web`, `auth`, `admin` | Detail pesanan di panel admin beserta kontrol status & pembayaran |
| `PATCH` | `/admin/orders/{order}/status` | `admin.orders.update-status` | `web`, `auth`, `admin` | Mengubah status pesanan (`preparing`, `ready`, `completed`, `cancelled`) |
| `POST` | `/admin/orders/{order}/mark-paid` | `admin.orders.mark-paid` | `web`, `auth`, `admin` | Menandai lunas (`paid`) pesanan `cash` yang berstatus `pending`/`preparing`/`ready` |
| `GET` | `/admin/menus` | `admin.menus.index` | `web`, `auth`, `admin` | Daftar seluruh menu di panel admin beserta tombol *quick-toggle* |
| `GET` | `/admin/menus/create` | `admin.menus.create` | `web`, `auth`, `admin` | Form tambah menu baru |
| `POST` | `/admin/menus` | `admin.menus.store` | `web`, `auth`, `admin` | Menyimpan menu baru dengan slug unik otomatis |
| `GET` | `/admin/menus/{menu}/edit` | `admin.menus.edit` | `web`, `auth`, `admin` | Form ubah data menu |
| `PUT` | `/admin/menus/{menu}` | `admin.menus.update` | `web`, `auth`, `admin` | Memperbarui data menu |
| `PATCH` | `/admin/menus/{menu}/toggle-availability` | `admin.menus.toggle-availability` | `web`, `auth`, `admin` | Membalik status `is_available` menu secara instan |
| `DELETE` | `/admin/menus/{menu}` | `admin.menus.destroy` | `web`, `auth`, `admin` | Menghapus menu tanpa merusak riwayat `order_items` |

---

## 4. Implementer's Choices (Intentionally Flexible)

- Tata letak navigasi panel Admin (apakah menggunakan topbar atau sidebar responsif) dan apakah tombol aksi transisi status diletakkan langsung di tabel daftar `/admin/orders`, di halaman detail `/admin/orders/{order}`, atau di keduanya, selama mengikuti tema Playful Pop-Editorial / Retro Coffee Club (`UI-1`) dan tidak menampilkan tombol mati (`UI-2`).
- Target URL redirect setelah mutasi admin berhasil (`admin.menus.index`, `admin.orders.index`, `admin.orders.show`, atau `back()`).
- Penanganan input checkbox `is_available` pada form Blade tambah/ubah menu (misal dengan hidden input `0` sebelum checkbox `1`, atau defaulting `true` saat field tidak dikirim pada `store` dan mempertahankan nilai lama saat tidak dikirim pada `update`).
- Penggunaan `get()` atau `paginate()` pada tampilan tabel daftar `/admin/orders` dan `/admin/menus`.
- Versi spesifik builder `vercel-php` di dalam `vercel.json` (misal `vercel-php@0.7.3` atau versi kompatibel terbaru).

---

## 5. Acceptance Criteria

1. **AC-1 (Daftar Pesanan, Filter Status & Pencarian `order_code` Admin)**: Admin dapat mengakses `GET /admin/orders`, memfilter daftar pesanan berdasarkan parameter `status`, serta mencari pesanan menggunakan parameter `search` secara *case-insensitive* dan parsial (pencarian `0042` maupun `gm-0042` menampilkan pesanan dengan `order_code = 'GM-0042'`).
2. **AC-2 (Penandaan Lunas Pesanan `cash` oleh Admin)**: Permintaan `POST /admin/orders/{order}/mark-paid` oleh Admin mengubah `payment_status` menjadi `'paid'` dan mengisi `paid_at` hanya jika `payment_method === 'cash'`, `payment_status === 'unpaid'`, dan `status` berada di `'pending'`, `'preparing'`, atau `'ready'`; permintaan yang sama ditolak jika `status` adalah `'cancelled'` atau `'completed'`, jika `payment_method === 'qris_dummy'`, atau jika pesanan sudah `'paid'`.
3. **AC-3 (Transisi Maju Berurutan & Penolakan Loncatan Status)**: Permintaan `PATCH /admin/orders/{order}/status` oleh Admin hanya mengizinkan transisi maju satu langkah (`pending -> preparing -> ready -> completed`) dan menolak setiap loncatan status (seperti `pending -> ready`, `pending -> completed`, `preparing -> completed`) maupun transisi mundur.
4. **AC-4 (Prasyarat Pembayaran pada Transisi `preparing` dan `completed`)**: Transisi `pending -> preparing` melalui `PATCH /admin/orders/{order}/status` diizinkan untuk pesanan `cash` yang masih `unpaid`, namun ditolak untuk pesanan `qris_dummy` yang masih `unpaid`; transisi `ready -> completed` selalu ditolak (baik `cash` maupun `qris_dummy`) apabila `payment_status` masih `'unpaid'`.
5. **AC-5 (Pembatalan Pesanan oleh Admin & Penanganan Pesanan `cash` Tidak Diambil)**: Permintaan `PATCH /admin/orders/{order}/status` dengan `status = 'cancelled'` oleh Admin berhasil membatalkan pesanan dari status `'pending'`, `'preparing'`, **maupun `'ready'`** selama `payment_status === 'unpaid'` (menangani pesanan `cash` yang sudah `ready` tetapi tidak diambil pelanggan), dan selalu menolak pembatalan apabila `payment_status === 'paid'` atau status sudah `'completed'`.
6. **AC-6 (CRUD Menu Admin, Validasi Harga & Slug Unik Otomatis)**: Admin dapat membuat (`POST /admin/menus`) dan memperbarui (`PUT /admin/menus/{menu}`) menu dengan pembuatan `slug` unik otomatis (termasuk ketika dua menu memiliki nama yang sama), serta sistem menolak input menu dengan `price < 1000` atau `category` di luar daftar kategori valid.
7. **AC-7 (Quick-Toggle Ketersediaan Menu)**: Permintaan `PATCH /admin/menus/{menu}/toggle-availability` oleh Admin mengubah nilai boolean `is_available` menu dari `true` ke `false` (dan sebaliknya) serta langsung tercermin pada katalog menu publik.
8. **AC-8 (Keamanan Riwayat Pesanan Saat Menu Dihapus Admin)**: Saat Admin menghapus menu melalui `DELETE /admin/menus/{menu}`, menu terhapus dari katalog, namun baris `order_items` pada pesanan yang pernah memesan menu tersebut tetap ada dengan `menu_id = null` serta nilai `menu_name`, `unit_price`, dan `subtotal` yang tetap utuh.
9. **AC-9 (Konfigurasi Serverless Vercel)**: Proyek memiliki file `vercel.json` dan `api/index.php` yang mengarahkan `VIEW_COMPILED_PATH`, `APP_CONFIG_CACHE`, dan `APP_ROUTES_CACHE` ke `/tmp`, serta mengatur `LOG_CHANNEL=stderr` dan `SESSION_DRIVER=cookie`.

---

## Implementation Notes

### Plan Approval & Deviations
- **Rencana Implementasi**: Disetujui secara eksplisit oleh pengguna pada Phase 1 sebelum penulisan kode dimulai.
- **Penyimpangan (Deviations)**: Tidak ada penyimpangan dari spesifikasi `RFC-003`.
- **Keputusan Implementasi (Sesuai Section 4 Implementer's Choices)**:
  - **Navigasi & Tata Letak Panel Admin**: Navbar utama (`resources/views/layouts/navigation.blade.php`) dan header halaman Admin menyediakan tautan langsung antara **Pesanan Masuk** (`/admin/orders`) dan **Kelola Katalog Menu** (`/admin/menus`). Tombol aksi pembayaran tunai (**"Tandai Lunas (Cash)"**) dan transisi status ditempatkan baik di kartu daftar `/admin/orders` maupun di halaman detail `/admin/orders/{order}`, dan hanya dirender untuk transisi yang valid menurut helper `Order::canMarkCashPaidByAdmin()` serta `Order::allowedAdminTargetStatuses()` (`ARCH-1`, `UI-2`).
  - **Target Redirect Setelah Mutasi Admin**:
    - `POST /admin/orders/{order}/mark-paid` dan `PATCH /admin/orders/{order}/status` menggunakan `redirect()->back(fallback: route('admin.orders.show', $order))` disertai flash `status`.
    - `POST /admin/menus`, `PUT /admin/menus/{menu}`, `PATCH /admin/menus/{menu}/toggle-availability`, dan `DELETE /admin/menus/{menu}` mengarahkan ke `route('admin.menus.index')` disertai flash `status`.
  - **Penanganan Checkbox `is_available`**: Form Blade menggunakan `<input type="hidden" name="is_available" value="0">` sebelum checkbox `value="1"`, sedangkan `AdminMenuController` memvalidasi `is_available` dengan `sometimes|boolean` (default `true` pada `store` jika field tidak dikirim, dan mempertahankan nilai lama `$menu->is_available` pada `update` jika field tidak dikirim).
  - **Rute `GET /admin` (`admin.dashboard`)**: Mengarahkan langsung ke `route('admin.orders.index')` (`/admin/orders`) sesuai Section 2.1 `RFC-003`, dan file placeholder `resources/views/admin/dashboard.blade.php` dihapus.
  - **Runtime Serverless Vercel**: `vercel.json` menggunakan `vercel-php@0.7.3` dengan pengalihan `APP_CONFIG_CACHE`, `APP_ROUTES_CACHE`, `APP_SERVICES_CACHE`, `APP_PACKAGES_CACHE`, dan `VIEW_COMPILED_PATH` ke `/tmp`, `LOG_CHANNEL=stderr`, dan `SESSION_DRIVER=cookie`; `api/index.php` membuat subdirektori `/tmp` sebelum memanggil `public/index.php`.
- **Fix Round (`REVIEW-RFC-003.md`)**:
  - Mengekstrak aturan validasi form menu pada `AdminMenuController::store()` dan `AdminMenuController::update()` ke satu metode helper privat `AdminMenuController::validateMenu(Request $request): array` di `app/Http/Controllers/Admin/AdminMenuController.php` untuk menghilangkan duplikasi aturan validasi, serta menutup follow-up terkait pada `RFCS.md` (`[CLOSED in RFC-003 fix round]`).

### Files Changed (Branch: `feat/rfc-003-admin-management-vercel-deploy`)

#### Created
- `app/Http/Controllers/Admin/AdminOrderController.php`
- `app/Http/Controllers/Admin/AdminMenuController.php`
- `resources/views/admin/orders/index.blade.php`
- `resources/views/admin/orders/show.blade.php`
- `resources/views/admin/menus/index.blade.php`
- `resources/views/admin/menus/create.blade.php`
- `resources/views/admin/menus/edit.blade.php`
- `api/index.php`
- `vercel.json`
- `tests/Feature/AdminOrderManagementTest.php`
- `tests/Feature/AdminOrderStateMachineTest.php`
- `tests/Feature/AdminMenuManagementTest.php`
- `tests/Feature/VercelConfigTest.php`

#### Modified
- `app/Models/Order.php`
- `routes/web.php`
- `resources/views/layouts/navigation.blade.php`
- `tests/Feature/AdminAccessTest.php`
- `RFCS.md`
- `RFCs/RFC-003-Admin-Management-Vercel-Deploy.md`

#### Deleted
- `resources/views/admin/dashboard.blade.php`

