# Code Review: RFC-003 — Admin Order & Menu Management and Vercel Serverless Configuration

- **RFC**: `RFCs/RFC-003-Admin-Management-Vercel-Deploy.md`
- **Features Covered**: `F10`, `F11`, `F12`, `F13`
- **Predecessors**: `RFC-002` (`Reviewed`)
- **Rules Checked**: `ARCH-1`, `DATA-1`, `DATA-3`, `SEC-1`, `SEC-3`, `UI-1`, `UI-2`, `TEST-1`, `OPS-1`
- **Product Type & Size**: `web app`, Size: `Small` (mengikuti `## Product Type` pada `PRD.md`; tidak ada pengecekan tambahan yang dilewati di luar daftar `PRD.md`).
- **Branch Reviewed**: `feat/rfc-003-admin-management-vercel-deploy`

---

## Step 0: Build, Test Suite, Fault Injection & Product Verification

### 1. Build & Test Suite Output

**Frontend Build (`npm run build`)**:
```text
> build
> vite build

vite v8.3.8 building client environment for production...
transforming...
✓ 4 modules transformed.
rendering chunks...
computing gzip size...
public/build/manifest.json             0.33 kB │ gzip:  0.16 kB
public/build/assets/app-B_IWscc-.css  61.57 kB │ gzip: 10.77 kB
public/build/assets/app-Dr67-uCa.js   54.32 kB │ gzip: 19.13 kB

✓ built in 1.93s
```

**Automated Test Suite (`php vendor/bin/phpunit --testdox`)**:
```text
PHPUnit 12.5.45 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.12
Configuration: D:\Projects\gumi-coffee\phpunit.xml

Time: 00:08.476, Memory: 64.00 MB

Admin Access (Tests\Feature\AdminAccess)
 ✔ Ac10 ensure user is admin middleware protects admin routes

Admin Menu Management (Tests\Feature\AdminMenuManagement)
 ✔ Ac6 admin menu crud price validation and automatic unique slug generation
 ✔ Ac7 admin can quick toggle menu availability and reflect on public catalog
 ✔ Ac8 deleting menu preserves order item snapshots and customer order history

Admin Order Management (Tests\Feature\AdminOrderManagement)
 ✔ Ac1 admin can list filter by status and search orders case insensitively while customers are forbidden
 ✔ Ac2 admin can mark cash orders as paid only in valid states

Admin Order State Machine (Tests\Feature\AdminOrderStateMachine)
 ✔ Ac3 sequential forward transitions succeed and skips or backward transitions are rejected
 ✔ Ac4 payment prerequisites for preparing and completed transitions
 ✔ Ac5 admin can cancel unpaid orders including ready unclaimed cash orders and never paid orders

Authentication (Tests\Feature\Auth\Authentication)
 ✔ Login screen can be rendered
 ✔ Users can authenticate using the login screen and redirect to menus by default
 ✔ Dashboard route redirects authenticated users to menus
 ✔ Users can not authenticate with invalid password
 ✔ Users can logout

Cart (Tests\Feature\Cart)
 ✔ Ac1 session cart operations and quantity validation
 ✔ Ac1 cart update rejects unavailable or missing items and empty cart redirects from checkout
 ✔ Ac2 guest cart persists after login and redirects to intended checkout

Checkout (Tests\Feature\Checkout)
 ✔ Ac3 checkout page displays takeaway info and validates notes max 500 chars
 ✔ Ac4 checkout is rejected outside operating hours in asia jakarta
 ✔ Ac5 checkout rechecks menu availability and rolls back atomically when menu unavailable or deleted
 ✔ Ac6 checkout calculates price purely on server generates order code saves snapshots and clears cart

Customer Order (Tests\Feature\CustomerOrder)
 ✔ Ac7 qris dummy simulation display and payment rules
 ✔ Ac8 customer order cancellation rules and payment vs cancel mutual exclusion
 ✔ Ac9 order policy isolates orders between customers
 ✔ Centralized order state machine methods enforce admin payment and transition matrix

Database Integrity (Tests\Feature\DatabaseIntegrity)
 ✔ Ac3 schema excludes out of scope columns and enforces relations and foreign key constraints

Database Seeder (Tests\Feature\DatabaseSeeder)
 ✔ Ac4 database seeder creates demo accounts and menus with existing static images

Email Verification (Tests\Feature\Auth\EmailVerification)
 ✔ Email verification screen can be rendered
 ✔ Email can be verified
 ✔ Email is not verified with invalid hash

Example (Tests\Feature\Example)
 ✔ The application returns a successful response

Example (Tests\Unit\Example)
 ✔ That true is true

Foundation And Catalog (Tests\Feature\FoundationAndCatalog)
 ✔ Ac1 timezone is asia jakarta and homepage reads central operating hours config
 ✔ Ac2 homepage displays brand story location and lihat menu cta to menus
 ✔ Ac5 public catalog is accessible by guest and renders categories rupiah prices and image fallback
 ✔ Ac6 unavailable menu displays unavailable badge and has no active order control
 ✔ Ac10 public pages use playful pop editorial palette sections semantic landmarks and single h1

Password Confirmation (Tests\Feature\Auth\PasswordConfirmation)
 ✔ Confirm password screen can be rendered
 ✔ Password can be confirmed
 ✔ Password is not confirmed with invalid password

Password Reset (Tests\Feature\Auth\PasswordReset)
 ✔ Reset password link screen can be rendered
 ✔ Reset password link can be requested
 ✔ Reset password screen can be rendered
 ✔ Password can be reset with valid token

Password Update (Tests\Feature\Auth\PasswordUpdate)
 ✔ Password can be updated
 ✔ Correct password must be provided to update password

Profile (Tests\Feature\Profile)
 ✔ Profile page is displayed and does not contain delete account section
 ✔ Profile information can be updated
 ✔ Email verification status is unchanged when the email address is unchanged
 ✔ Delete profile endpoint is disabled and does not delete user

Registration (Tests\Feature\Auth\Registration)
 ✔ Registration screen can be rendered
 ✔ New users can register and are forced to customer role even if admin role is injected

Vercel Config (Tests\Feature\VercelConfig)
 ✔ Ac9 vercel json and api index configure serverless runtime paths

OK (53 tests, 518 assertions)
```

### 2. Fault Injection Verification (`AC-1` s/d `AC-9` & Critical Properties)

Setiap kriteria penerimaan (`AC-1` s/d `AC-9`) dan properti bisnis kritis (`G-2`, `G-3`, `ARCH-1`, `DATA-1`, `DATA-3`, `SEC-1`, `SEC-3`, `OPS-1`) dirusak secara sengaja pada kode implementasi, dijalankan terhadap PHPUnit untuk memastikan pengujian benar-benar gagal (**FAIL**), lalu dikembalikan ke kondisi semula:

| Criterion / Property | What Was Broken (Fault Injected) | Caught by Test? | Failing Test |
|---|---|---|---|
| **AC-1a** (Filter status pada `GET /admin/orders?status=`) | Mengubah `if ($activeStatus !== null)` menjadi `if (false)` di `app/Http/Controllers/Admin/AdminOrderController.php:35` | **YES** (`exit 1`) | `AdminOrderManagementTest::test_ac1_admin_can_list_filter_by_status_and_search_orders_case_insensitively_while_customers_are_forbidden` |
| **AC-1b** (Pencarian `order_code` *case-insensitive* & parsial pada `GET /admin/orders?search=`) | Mengubah `$query->whereRaw('LOWER(order_code) LIKE ?', ['%'.strtolower($search).'%'])` menjadi `$query->where('order_code', $search)` di `app/Http/Controllers/Admin/AdminOrderController.php:40` | **YES** (`exit 1`) | `AdminOrderManagementTest::test_ac1_admin_can_list_filter_by_status_and_search_orders_case_insensitively_while_customers_are_forbidden` |
| **AC-2** (Penandaan lunas pesanan `cash` & penolakan untuk `qris_dummy` — `DATA-1`) | Mengubah `$this->payment_method === self::METHOD_CASH` menjadi `true` pada `Order::canMarkCashPaidByAdmin()` di `app/Models/Order.php:122` | **YES** (`exit 1`) | `AdminOrderManagementTest::test_ac2_admin_can_mark_cash_orders_as_paid_only_in_valid_states` |
| **AC-3** (Transisi maju berurutan & penolakan loncatan status — `ARCH-1`, `DATA-1`) | Mengizinkan loncatan `pending -> ready` dengan mengubah syarat `self::STATUS_READY` di `app/Models/Order.php:136` | **YES** (`exit 1`) | `AdminOrderStateMachineTest::test_ac3_sequential_forward_transitions_succeed_and_skips_or_backward_transitions_are_rejected` |
| **AC-4a** (Penolakan `pending -> preparing` untuk pesanan `qris_dummy` yang masih `unpaid` — `ARCH-1`) | Menghapus pengecekan `($this->payment_method === self::METHOD_CASH || $this->payment_status === self::PAYMENT_PAID)` di `app/Models/Order.php:135` | **YES** (`exit 1`) | `AdminOrderStateMachineTest::test_ac4_payment_prerequisites_for_preparing_and_completed_transitions` |
| **AC-4b** (Kewajiban `payment_status === 'paid'` untuk transisi `ready -> completed` — `ARCH-1`) | Menghapus `&& $this->payment_status === self::PAYMENT_PAID` pada cabang `self::STATUS_COMPLETED` di `app/Models/Order.php:138` | **YES** (`exit 1`) | `AdminOrderStateMachineTest::test_ac4_payment_prerequisites_for_preparing_and_completed_transitions` |
| **AC-5a** (Pencegahan *deadlock* pesanan `cash` tidak diambil: izinkan Admin membatalkan `ready + unpaid` — `G-2`) | Menghapus `self::STATUS_READY` dari daftar status yang boleh dibatalkan Admin di `app/Models/Order.php:140` | **YES** (`exit 1`) | `AdminOrderStateMachineTest::test_ac5_admin_can_cancel_unpaid_orders_including_ready_unclaimed_cash_orders_and_never_paid_orders` |
| **AC-5b** (Larangan mutlak pembatalan pesanan yang sudah `paid` oleh Admin — `G-2`, `DATA-1`) | Menghapus syarat `$this->payment_status === self::PAYMENT_UNPAID` pada cabang `self::STATUS_CANCELLED` di `app/Models/Order.php:139` | **YES** (`exit 1`) | `AdminOrderStateMachineTest::test_ac5_admin_can_cancel_unpaid_orders_including_ready_unclaimed_cash_orders_and_never_paid_orders` |
| **AC-6a** (Pembuatan `slug` unik otomatis saat nama menu duplikat — `SEC-3`) | Mengganti `$this->generateUniqueSlug($validated['name'])` dengan `Str::slug($validated['name'])` di `app/Http/Controllers/Admin/AdminMenuController.php:52` | **YES** (`exit 1`) | `AdminMenuManagementTest::test_ac6_admin_menu_crud_price_validation_and_automatic_unique_slug_generation` |
| **AC-6b** (Validasi `price >= 1000` pada tambah/ubah menu — `SEC-3`) | Mengubah `'min:1000'` menjadi `'min:100'` di `app/Http/Controllers/Admin/AdminMenuController.php:47` | **YES** (`exit 1`) | `AdminMenuManagementTest::test_ac6_admin_menu_crud_price_validation_and_automatic_unique_slug_generation` |
| **AC-7** (*Quick-toggle* ketersediaan menu `PATCH /admin/menus/{menu}/toggle-availability`) | Mengubah `'is_available' => ! $menu->is_available` menjadi `'is_available' => $menu->is_available` di `app/Http/Controllers/Admin/AdminMenuController.php:121` | **YES** (`exit 1`) | `AdminMenuManagementTest::test_ac7_admin_can_quick_toggle_menu_availability_and_reflect_on_public_catalog` |
| **AC-8** (Keutuhan snapshot `order_items` saat menu dihapus melalui `DELETE /admin/menus/{menu}` — `DATA-3`, `G-3`) | Menambahkan `$menu->orderItems()->delete();` sebelum `$menu->delete();` di `app/Http/Controllers/Admin/AdminMenuController.php:134` | **YES** (`exit 2`) | `AdminMenuManagementTest::test_ac8_deleting_menu_preserves_order_item_snapshots_and_customer_order_history` |
| **AC-9** (Konfigurasi serverless Vercel `vercel.json` & `api/index.php` — `OPS-1`) | Mengubah `"SESSION_DRIVER": "cookie"` menjadi `"SESSION_DRIVER": "file"` di `vercel.json:34` | **YES** (`exit 1`) | `VercelConfigTest::test_ac9_vercel_json_and_api_index_configure_serverless_runtime_paths` |

### 3. Product Usage & User Journey Verification

Seluruh layar dan alur operasional Admin dari `PRD.md` (User Journey 1 & 2) diverifikasi:
- **Navigasi & Redirect Admin (`GET /admin`)**: Saat login sebagai `admin@gumicoffee.test`, mengakses `GET /admin` langsung mengarahkan ke `/admin/orders`, dan navbar (`resources/views/layouts/navigation.blade.php:59-68,147-156`) menampilkan tautan aktif **Pesanan Admin** dan **Kelola Menu** baik pada tampilan desktop maupun menu kolaps mobile.
- **Dashboard & Kontrol Pesanan (`/admin/orders` & `/admin/orders/{order}`)**:
  - Form pencarian `order_code` (`<label for="admin-order-search">`) dan tab filter cepat status (`Semua Status`, `Pending`, `Preparing`, `Ready`, `Completed`, `Cancelled`) mempertahankan parameter kueri lintas filter.
  - Tombol aksi pembayaran tunai (**"Tandai Lunas (Cash)"**) dan tombol transisi status hanya dirender untuk transisi yang diizinkan oleh state machine (`$order->canMarkCashPaidByAdmin()` dan `$order->allowedAdminTargetStatuses()`), sehingga untuk pesanan `cash` yang berstatus `ready + unpaid` tampil tiga opsi yang valid: **"Tandai Lunas (Cash)"** dan **"Batalkan Pesanan"** (sedangkan tombol **"Selesaikan (Completed)"** baru muncul setelah pembayaran ditandai lunas).
- **Manajemen Katalog Menu (`/admin/menus`, `/admin/menus/create`, `/admin/menus/{menu}/edit`)**:
  - Kartu menu di `/admin/menus` menampilkan kategori, nama, `slug`, badge `Tersedia` / `Tidak Tersedia`, deskripsi, harga Rupiah, tombol *quick-toggle* (`Matikan Ketersediaan (Set Habis)` / `Aktifkan Ketersediaan (Set Tersedia)`), tautan `Ubah Menu`, dan tombol `Hapus Menu`.
  - Seluruh 5 view Admin memuat tepat satu `<h1>`, menggunakan `<label>` untuk setiap input form, bebas dari tautan mati (`href="#"`) maupun komentar `TODO`.

---

## Review Dimensions

### 1. RFC ADHERENCE
- **Verdict**: PASS
- **Findings**:
  - Seluruh 9 kriteria penerimaan (`AC-1` s/d `AC-9`) dan ke-11 rute pada Section 3 `RFC-003` (beserta pengalihan `GET /admin` ke `route('admin.orders.index')` sesuai Section 2.1) telah diimplementasikan sesuai spesifikasi tanpa penyimpangan.
  - Bagian `## Implementation Notes` pada `RFCs/RFC-003-Admin-Management-Vercel-Deploy.md:119-160` mencatat persetujuan rencana, pilihan implementasi sesuai Section 4, serta daftar lengkap file yang dibuat, diubah, dan dihapus.
- **Suggestions**: Tidak ada.

### 2. RULES COMPLIANCE
- **Verdict**: PASS
- **Findings**:
  - **ARCH-1 & DATA-1**: Seluruh aturan transisi status dan penandaan lunas tunai dipusatkan di `app/Models/Order.php:120-202` (`canMarkCashPaidByAdmin()`, `canTransitionToByAdmin()`, `allowedAdminTargetStatuses()`, `markCashPaidByAdmin()`, `transitionStatusByAdmin()`). `AdminOrderController` (`app/Http/Controllers/Admin/AdminOrderController.php:62-82`) maupun view Blade (`resources/views/admin/orders/index.blade.php:164-184`, `resources/views/admin/orders/show.blade.php:174-230`) hanya memanggil metode model tersebut tanpa menduplikasi logika syarat transisi. Setiap mutasi berjalan di dalam `DB::transaction()` dengan `lockForUpdate()` dan memvalidasi ulang state setelah *lock* diperoleh.
  - **DATA-3**: Penghapusan menu pada `AdminMenuController::destroy` (`app/Http/Controllers/Admin/AdminMenuController.php:131-139`) bekerja selaras dengan constraint `nullOnDelete()` pada `order_items.menu_id`, menjaga salinan `menu_name`, `unit_price`, dan `subtotal` pada pesanan historis.
  - **SEC-1 & SEC-3**: Seluruh rute `/admin/*` berada di dalam grup `Route::middleware(['auth', 'admin'])->prefix('admin')` (`routes/web.php:48-65`). Seluruh form mutasi menyertakan `@csrf`, dan `AdminMenuController` memvalidasi `price` sebagai `required|integer|min:1000`, `category` terhadap `config('gumi.categories')`, serta menghasilkan `slug` unik otomatis melalui `generateUniqueSlug()` (`app/Http/Controllers/Admin/AdminMenuController.php:141-162`).
  - **UI-1 & UI-2**: Kelima view Admin menerapkan tema Playful Pop-Editorial / Retro Coffee Club (`#b22e3b`, `#fffaee`, `#e9bfd2`, `#020202`) dengan kontras teks WCAG AA, satu `<h1>` per halaman, `<label>` terhubung ke setiap input form, dan tanpa tombol atau tautan mati.
  - **TEST-1**: Seluruh pengujian otomatis yang direncanakan untuk `RFC-003` pada `TEST-STRATEGY.md` telah diimplementasikan (`AdminOrderManagementTest`, `AdminOrderStateMachineTest`, `AdminMenuManagementTest`, `VercelConfigTest`, serta pembaruan `AdminAccessTest`) dan lulus 100%.
  - **OPS-1**: `vercel.json` (`vercel.json:1-37`) dan `api/index.php` (`api/index.php:1-30`) mengarahkan `VIEW_COMPILED_PATH`, `APP_CONFIG_CACHE`, dan `APP_ROUTES_CACHE` ke `/tmp`, menetapkan `LOG_CHANNEL=stderr` serta `SESSION_DRIVER=cookie`, dan menyiapkan subdirektori `/tmp` sebelum memuat `public/index.php`.
- **Suggestions**: Tidak ada.

### 3. SECURITY
- **Verdict**: PASS
- **Findings**:
  - **Authorization Boundary**: Middleware `EnsureUserIsAdmin` menolak seluruh permintaan `customer` ke endpoint `GET/POST/PUT/PATCH/DELETE` di bawah `/admin/orders*` dan `/admin/menus*` dengan HTTP `403 Forbidden`.
  - **Parameterized Search Query**: Pencarian `order_code` pada `app/Http/Controllers/Admin/AdminOrderController.php:39-41` menggunakan parameter binding (`whereRaw('LOWER(order_code) LIKE ?', ['%'.strtolower($search).'%'])`) sehingga aman dari SQL injection.
  - **Input Validation**: `AdminMenuController::store` dan `update` membatasi `category` secara ketat menggunakan `Rule::in($categories)` dari `config('gumi.categories')`, membatasi panjang `name` (`max:255`), `description` (`max:1000`), dan `image_path` (`max:2048`), serta memaksa `price` berupa integer minimal `1000`.
- **Suggestions**: Tidak ada.

### 4. PERFORMANCE
- **Verdict**: PASS
- **Findings**:
  - `AdminOrderController::index` (`app/Http/Controllers/Admin/AdminOrderController.php:31-33`) melakukan *eager loading* `->with(['user', 'items'])`, dan `AdminOrderController::show` (`app/Http/Controllers/Admin/AdminOrderController.php:55`) memuat `$order->load(['user', 'items'])`, sehingga daftar maupun detail pesanan Admin tidak memicu query N+1 saat merender data pelanggan dan rincian item pesanan.
- **Suggestions**: Tidak ada.

### 5. MAINTAINABILITY
- **Verdict**: PASS
- **Findings**:
  - Struktur kode antara `AdminOrderController`, `AdminMenuController`, helper state machine pada `App\Models\Order`, serta view Blade sangat rapi dan konsisten dengan konvensi `RFC-001` dan `RFC-002`.
  - *Temuan non-blocking*: Pada `app/Http/Controllers/Admin/AdminMenuController.php:41-50` dan `84-93`, aturan validasi form menu (`name`, `category`, `description`, `price`, `image_path`, `is_available`) ditulis berulang pada metode `store()` dan `update()`.
- **Suggestions**:
  - Ekstrak aturan validasi menu pada `app/Http/Controllers/Admin/AdminMenuController.php:41-50,84-93` ke satu metode helper privat `validateMenu(Request $request): array` di dalam `AdminMenuController` agar definisi aturan validasi menu berada di satu tempat apabila ada penyesuaian aturan di masa depan.

---

## Overall Assessment

**Overall verdict**: PASS

- **Overall Risk Level**: Low
- **Summary**: Implementasi `RFC-003` memenuhi seluruh 9 kriteria penerimaan (`AC-1` s/d `AC-9`), mematuhi seluruh aturan `RULES.md` (`ARCH-1`, `DATA-1`, `DATA-3`, `SEC-1`, `SEC-3`, `UI-1`, `UI-2`, `TEST-1`, `OPS-1`), dan melengkapi seluruh pengujian otomatis yang direncanakan pada `TEST-STRATEGY.md`. Seluruh 53 test (518 assertion) lulus 100% dan terbukti menangkap regresi pada setiap kriteria penerimaan melalui pengujian *fault-injection*.
- **Blocking Issues**: None.
- **Improvement Suggestions**:
  1. **(RFC-003)** Ekstrak aturan validasi form menu yang identik pada `AdminMenuController::store()` (`app/Http/Controllers/Admin/AdminMenuController.php:41-50`) dan `AdminMenuController::update()` (`app/Http/Controllers/Admin/AdminMenuController.php:84-93`) ke dalam satu metode privat `validateMenu(Request $request): array` untuk menghindari duplikasi aturan validasi.
