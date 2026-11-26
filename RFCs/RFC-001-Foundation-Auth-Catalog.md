# RFC-001: Project Foundation, Authentication, Schema & Public Catalog

**Features**: F1, F2, F3, F4
**Depends on**: none
**Rules**: ARCH-2, DATA-3, SEC-2, UI-1, UI-2, TEST-1
**Complexity**: Low

## 1. Summary

RFC ini membangun fondasi aplikasi **Gumi Coffee**: inisialisasi Laravel dengan zona waktu `Asia/Jakarta` dan konfigurasi terpusat `config/gumi.php`, skema migrasi keempat tabel inti (`users`, `menus`, `orders`, `order_items`) beserta model Eloquent dan relasinya, `DatabaseSeeder` dengan akun demo dan menu contoh beraset gambar statis di `public/`, autentikasi Laravel Breeze (`customer` & `admin`) dengan fitur hapus akun yang dinonaktifkan sepenuhnya, tata letak dasar bergaya **Urban / Neo-Brutalist**, halaman Beranda (*Brand Profile* ringan), serta halaman Katalog Menu Publik.

---

## 2. Technical Approach & Architecture

### 2.1 Central Configuration (`config/gumi.php` & `config/app.php`)
- `config/app.php` menetapkan `'timezone' => 'Asia/Jakarta'` (`ARCH-2`).
- `config/gumi.php` menjadi sumber tunggal untuk parameter operasional kedai:
  ```php
  return [
      'operating_hours' => [
          'open'  => env('GUMI_OPEN_TIME', '08:00'),
          'close' => env('GUMI_CLOSE_TIME', '22:00'),
      ],
      'estimated_pickup_text' => env('GUMI_ESTIMATED_PICKUP_TEXT', 'Estimasi siap 10-15 menit'),
      'categories' => ['Coffee', 'Non-Coffee', 'Pastry'],
      'location' => 'Jl. Braga No. 42, Bandung',
  ];
  ```

### 2.2 Database Schema & Models
Seluruh 4 tabel inti dibuat pada RFC-001 agar batasan *foreign key* (`orders.user_id` dengan `restrictOnDelete()` dan `order_items.menu_id` dengan `nullOnDelete()`) langsung aktif sejak awal (`DATA-3`):

1. **`users`**:
   - `id` (`bigint`, PK)
   - `name` (`string`)
   - `email` (`string`, `unique`)
   - `email_verified_at` (`timestamp`, `nullable`)
   - `password` (`string`)
   - `role` (`string`, default `'customer'`; nilai valid: `'customer'`, `'admin'`)
   - `remember_token`, `timestamps`
2. **`menus`**:
   - `id` (`bigint`, PK)
   - `name` (`string`)
   - `slug` (`string`, `unique`)
   - `category` (`string`)
   - `description` (`text`, `nullable`)
   - `price` (`unsignedInteger`)
   - `image_path` (`string`, `nullable`)
   - `is_available` (`boolean`, default `true`)
   - `timestamps`
3. **`orders`** *(tanpa kolom `order_type` dan `table_id` sesuai `DATA-3`)*:
   - `id` (`bigint`, PK)
   - `order_code` (`string(20)`, `nullable`, `unique`)
   - `user_id` (`foreignId`, constrained to `users.id`, `restrictOnDelete()`)
   - `total` (`unsignedInteger`)
   - `status` (`string`, default `'pending'`; nilai: `'pending'`, `'preparing'`, `'ready'`, `'completed'`, `'cancelled'`)
   - `payment_method` (`string`; nilai: `'cash'`, `'qris_dummy'`)
   - `payment_status` (`string`, default `'unpaid'`; nilai: `'unpaid'`, `'paid'`)
   - `paid_at` (`timestamp`, `nullable`)
   - `notes` (`text`, `nullable`)
   - `timestamps`
4. **`order_items`**:
   - `id` (`bigint`, PK)
   - `order_id` (`foreignId`, constrained to `orders.id`, `cascadeOnDelete()`)
   - `menu_id` (`foreignId`, `nullable`, constrained to `menus.id`, `nullOnDelete()`)
   - `menu_name` (`string`)
   - `quantity` (`unsignedInteger`)
   - `unit_price` (`unsignedInteger`)
   - `subtotal` (`unsignedInteger`)
   - `timestamps`

### 2.3 Authentication, Redirects & Profile Constraints
- Menggunakan **Laravel Breeze** (Blade + Tailwind CSS + Alpine.js).
- `RegisteredUserController::store` menetapkan `'role' => 'customer'` secara eksplisit saat membuat `User` (`SEC-2`).
- **Default Post-Auth Redirect & Route `/dashboard`**:
  - Setelah login (`POST /login`) atau registrasi (`POST /register`), apabila tidak ada `url.intended` di session, pengguna diarahkan secara default ke `route('menus.index')` (`/menus`).
  - Rute `GET /dashboard` (`route('dashboard')`, jika tetap dipertahankan untuk kompatibilitas Breeze) melakukan redirect langsung ke `route('menus.index')` (`/menus`), sehingga tidak ada halaman template Breeze `/dashboard` kosong.
- Fitur **Delete Account** bawaan Breeze dihapus sepenuhnya:
  - Rute `DELETE /profile` (`profile.destroy`) dihapus dari `routes/web.php`.
  - Metode `ProfileController::destroy` dihapus atau dinonaktifkan.
  - Partial Blade `profile/partials/delete-user-form.blade.php` tidak dirender di halaman `/profile` (`SEC-2`).

### 2.4 Database Seeder (`DatabaseSeeder`)
Menyediakan data awal yang deterministik untuk pengujian dan demo portofolio:
- **Akun Admin Demo**: `name = 'Admin Gumi'`, `email = 'admin@gumicoffee.test'`, `password = 'password'`, `role = 'admin'`.
- **Akun Customer Demo**: `name = 'Pelanggan Gumi'`, `email = 'customer@gumicoffee.test'`, `password = 'password'`, `role = 'customer'`.
- **Data Menu Contoh**: Minimal 6 menu yang tersebar di kategori `Coffee`, `Non-Coffee`, dan `Pastry` (termasuk minimal 5 menu dengan `is_available = true` dan minimal 1 menu dengan `is_available = false`), menggunakan aset gambar statis di `public/images/menus/` sehingga tampil tanpa bergantung pada *upload storage* lokal.

### 2.5 Public Routes & UI Theme (Urban / Neo-Brutalist)
- **Routes**:
  - `GET /` (`home`): Menampilkan *Brand Profile* Gumi Coffee (cerita singkat brand, lokasi dari `config('gumi.location')`, jam operasional `'open' - 'close' WIB` dari `config('gumi.operating_hours')`), dan tombol CTA **"Lihat Menu"** yang mengarah ke `route('menus.index')` (`/menus`).
  - `GET /menus` (`menus.index`): Menampilkan seluruh daftar menu yang dikelompokkan berdasarkan kategori (`config('gumi.categories')`).
- **Urban Visual Direction (`UI-1`, `UI-2`)**:
  - Palet warna:
    - Primary Red: `#b22e3b` (dengan teks `#fffaee`)
    - Primary Cream Background: `#fffaee` (dengan teks `#020202`)
    - Secondary Pink Accent: `#e9bfd2` (dengan teks `#020202`)
    - Dark Neutral: `#020202` (untuk teks, border tegas `2px solid #020202`, dan *hard offset shadow* seperti `4px 4px 0px #020202`).
  - Dokumen HTML memiliki `<html lang="id">`, `<title>` unik, landmark semantik (`header`, `nav`, `main`, `footer`), dan tepat satu `<h1>` per halaman.

---

## 3. Interfaces Provided to Downstream RFCs

- **Eloquent Models, Relations & Casts**:
  - `App\Models\User`:
    - Kolom fillable: `name`, `email`, `password`, `role`.
    - Helper: `isAdmin(): bool` (mengembalikan `$this->role === 'admin'`).
    - Relasi: `orders(): HasMany` (ke `Order::class`).
  - `App\Models\Menu`:
    - Kolom fillable: `name`, `slug`, `category`, `description`, `price`, `image_path`, `is_available`.
    - Casts: `'price' => 'integer'`, `'is_available' => 'boolean'`.
    - Relasi: `orderItems(): HasMany` (ke `OrderItem::class`).
  - `App\Models\Order`:
    - Kolom fillable: `order_code`, `user_id`, `total`, `status`, `payment_method`, `payment_status`, `paid_at`, `notes`.
    - Casts: `'total' => 'integer'`, `'paid_at' => 'datetime'`.
    - Relasi: `user(): BelongsTo` (ke `User::class`), `items(): HasMany` (ke `OrderItem::class`, foreign key `'order_id'`).
  - `App\Models\OrderItem`:
    - Kolom fillable: `order_id`, `menu_id`, `menu_name`, `quantity`, `unit_price`, `subtotal`.
    - Casts: `'quantity' => 'integer'`, `'unit_price' => 'integer'`, `'subtotal' => 'integer'`.
    - Relasi: `order(): BelongsTo` (ke `Order::class`), `menu(): BelongsTo` (ke `Menu::class`).
- **Config Keys**:
  - `config('gumi.operating_hours.open')` & `config('gumi.operating_hours.close')`
  - `config('gumi.estimated_pickup_text')`
  - `config('gumi.categories')`
  - `config('gumi.location')`
- **Catalog Status Display in RFC-001 vs Action Form in RFC-002**:
  - Pada `RFC-001`, setiap kartu menu di `GET /menus` menampilkan badge status ketersediaan (**"Tersedia"** untuk `is_available = true` dan **"Habis / Tidak Tersedia"** untuk `is_available = false`) tanpa tombol mati agar mematuhi `UI-2`. Pada `RFC-002`, kartu menu di `GET /menus` dilengkapi form aksi `POST /cart/items` (aktif untuk `is_available = true`, dan tombol `disabled` untuk `is_available = false`).

---

## 4. Implementer's Choices (Intentionally Flexible)

Hal-hal berikut tidak mengikat perilaku antarmuka dan diserahkan kepada implementer:
- Teks narasi cerita singkat Gumi Coffee di beranda dan nama/deskripsi spesifik menu di dalam `DatabaseSeeder` (selama mencakup kategori `Coffee`, `Non-Coffee`, `Pastry`, menggunakan harga Rupiah bulat `>= 1000`, dan menyertakan contoh menu `is_available = true` serta `is_available = false`).
- Format spasi pemisah simbol Rupiah pada tampilan harga menu (misal `'Rp ' . number_format($price, 0, ',', '.')` menjadi `Rp 25.000` atau `Rp25.000`).
- Desain grafis SVG/PNG pada file gambar statis di `public/images/menus/` dan tampilan *fallback placeholder* saat `image_path` bernilai `null`.
- Pemilihan jenis huruf (*font family*) pendamping gaya Urban / Neo-Brutalist, selama kontras warna dan keterbacaan memenuhi `UI-1`.

---

## 5. Acceptance Criteria

1. **AC-1 (Konfigurasi Zona Waktu & Jam Buka Terpusat)**: Nilai `config('app.timezone')` adalah `'Asia/Jakarta'`, dan `config('gumi.operating_hours')` menyediakan jam `'open'` serta `'close'` dalam format `HH:MM` yang ditampilkan secara identik pada halaman Beranda (`GET /`).
2. **AC-2 (Halaman Beranda & Brand Profile)**: Permintaan `GET /` mengembalikan HTTP `200`, menampilkan cerita singkat Gumi Coffee, lokasi kedai, jam operasional dari konfigurasi, serta tautan CTA **"Lihat Menu"** yang mengarah ke `/menus`.
3. **AC-3 (Skema Database, Model Relasi & Foreign Key Integrity)**: Migrasi database membuat tabel `users`, `menus`, `orders` (tanpa kolom `order_type` atau `table_id`), dan `order_items` beserta model Eloquent dengan relasi `User::orders()`, `Order::user()`, `Order::items()`, `OrderItem::order()`, dan `OrderItem::menu()`; upaya menghapus baris `users` yang sudah memiliki baris di `orders` ditolak oleh constraint database (`restrictOnDelete()`), sedangkan menghapus baris `menus` yang direferensikan oleh `order_items` mengubah `order_items.menu_id` menjadi `null` (`nullOnDelete()`) tanpa menghapus baris `order_items`.
4. **AC-4 (Database Seeder Siap Uji)**: Menjalankan `php artisan db:seed` membuat akun Admin demo (`admin@gumicoffee.test`, `role = 'admin'`), akun Customer demo (`customer@gumicoffee.test`, `role = 'customer'`), serta daftar menu contoh di kategori `Coffee`, `Non-Coffee`, dan `Pastry` yang mereferensikan file gambar statis yang benar-benar ada di dalam `public/`.
5. **AC-5 (Halaman Katalog Menu Publik)**: Permintaan `GET /menus` dapat diakses oleh pengunjung tanpa login (HTTP `200`), menampilkan menu yang dikelompokkan berdasarkan kategori beserta nama, deskripsi, harga dalam format Rupiah, dan gambar (atau visual *fallback placeholder* jika `image_path` bernilai `null`).
6. **AC-6 (Indikator Menu Tidak Tersedia di Katalog)**: Pada halaman `GET /menus`, setiap menu dengan `is_available = false` menampilkan label status tidak tersedia/habis secara jelas dan tidak menyediakan kontrol aktif untuk memesan menu tersebut.
7. **AC-7 (Registrasi Publik Menetapkan Role Customer)**: Pendaftaran akun baru melalui `POST /register` (bahkan jika request menyertakan input `role = 'admin'`) selalu membuat pengguna baru di database dengan `role = 'customer'` dan mengarahkan pengguna ke `/menus`.
8. **AC-8 (Login, Logout & Pembaruan Profil Pelanggan)**: Pengguna terdaftar dapat melakukan login (`POST /login`, diarahkan secara default ke `/menus` jika tidak ada `url.intended`), logout (`POST /logout`), serta memperbarui nama, email (`PATCH /profile`), dan password (`PUT /password`) ketika sudah login.
9. **AC-9 (Fitur Hapus Akun Dinonaktifkan)**: Halaman `GET /profile` tidak menampilkan form atau tombol hapus akun, dan permintaan `DELETE /profile` tidak menghapus data pengguna di tabel `users` (mengembalikan `404 Not Found` atau `405 Method Not Allowed`).
10. **AC-10 (Tema Urban, Semantik HTML & Responsivitas)**: Halaman publik (`/` dan `/menus`) menerapkan palet warna `#b22e3b`, `#fffaee`, `#e9bfd2`, dan `#020202` sesuai aturan kontras `UI-1`, memiliki struktur landmark HTML semantik (`header`, `nav`, `main`, `footer`, satu `h1`), dan tidak mengalami *horizontal overflow* pada viewport `375px`, `768px`, dan `1280px`.
