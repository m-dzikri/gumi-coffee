# Code Review: RFC-002 — Session Cart, Takeaway Checkout, Order State Machine & Customer Orders

- **RFC**: `RFCs/RFC-002-Cart-Checkout-Customer-Orders.md`
- **Features Covered**: `F5`, `F6`, `F7`, `F8`, `F9`
- **Predecessors**: `RFC-001` (`Reviewed`)
- **Rules Checked**: `ARCH-1`, `ARCH-2`, `DATA-1`, `DATA-2`, `DATA-3`, `SEC-1`, `SEC-3`, `UI-1`, `UI-2`, `TEST-1`
- **Product Type & Size**: `web app`, Size: `Small` (mengikuti `## Product Type` pada `PRD.md`; tidak ada pengecekan tambahan yang dilewati di luar daftar `PRD.md`).
- **Branch Reviewed**: `feat/rfc-002-cart-checkout-customer-orders`

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
public/build/assets/app-CxnNbk6X.css  59.91 kB │ gzip: 10.49 kB
public/build/assets/app-Dr67-uCa.js   54.32 kB │ gzip: 19.13 kB

✓ built in 1.94s
```

**Automated Test Suite (`php vendor/bin/phpunit --testdox`)**:
```text
PHPUnit 12.5.45 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.12
Configuration: D:\Projects\gumi-coffee\phpunit.xml

Time: 00:04.268, Memory: 66.00 MB

Admin Access (Tests\Feature\AdminAccess)
 ✔ Ac10 ensure user is admin middleware protects admin routes

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

OK (44 tests, 345 assertions)
```

### 2. Fault Injection Verification (`AC-1` s/d `AC-10` & State Machine Properties)

Setiap kriteria penerimaan (`AC-1` s/d `AC-10`) dan properti bisnis kritis (`G-2`, `G-3`, `ARCH-1`, `DATA-1`, `DATA-2`, `SEC-1`, `SEC-3`) dirusak secara sengaja pada kode implementasi, dijalankan terhadap PHPUnit untuk memastikan pengujian benar-benar gagal (**FAIL**), lalu dikembalikan ke kondisi semula:

| Criterion / Property | What Was Broken (Fault Injected) | Caught by Test? | Failing Test |
|---|---|---|---|
| **AC-1a** (Penolakan menu `is_available = false` saat tambah ke keranjang) | Mengubah `if (! $menu->is_available)` menjadi `if (false)` di `app/Http/Controllers/CartController.php:73` | **YES** (`exit 1`) | `CartTest::test_ac1_session_cart_operations_and_quantity_validation` |
| **AC-1b** (`DELETE /cart/items/{menuId}` menghapus item dari session) | Mengomentari `unset($cart[$menuId]);` di `app/Http/Controllers/CartController.php:144` | **YES** (`exit 1`) | `CartTest::test_ac1_session_cart_operations_and_quantity_validation` |
| **AC-2** (Keranjang tamu bertahan setelah login & redirect ke `url.intended` `/checkout`) | Mengubah `redirect()->intended(route('menus.index', absolute: false))` menjadi `redirect(route('menus.index', absolute: false))` di `app/Http/Controllers/Auth/AuthenticatedSessionController.php:31` | **YES** (`exit 1`) | `CartTest::test_ac2_guest_cart_persists_after_login_and_redirects_to_intended_checkout` |
| **AC-3** (Validasi `notes` maksimal 500 karakter pada checkout — `SEC-3`) | Mengubah `'max:500'` menjadi `'max:1000'` di `app/Http/Controllers/CheckoutController.php:69` | **YES** (`exit 1`) | `CheckoutTest::test_ac3_checkout_page_displays_takeaway_info_and_validates_notes_max_500_chars` |
| **AC-4** (Penolakan checkout di luar jam operasional `Asia/Jakarta` — `ARCH-2`) | Mengubah `if ($nowTime < $openTime || $nowTime > $closeTime)` menjadi `if (false)` di `app/Http/Controllers/CheckoutController.php:76` | **YES** (`exit 1`) | `CheckoutTest::test_ac4_checkout_is_rejected_outside_operating_hours_in_asia_jakarta` |
| **AC-5** (Pengecekan ulang `is_available` di dalam transaksi checkout & rollback atomik — `DATA-2`, `G-2`) | Mengubah `if (! $menu || ! $menu->is_available)` menjadi `if (! $menu)` di `app/Http/Controllers/CheckoutController.php:107` | **YES** (`exit 1`) | `CheckoutTest::test_ac5_checkout_rechecks_menu_availability_and_rolls_back_atomically_when_menu_unavailable_or_deleted` |
| **AC-6** (Kalkulasi harga murni di server & pembuatan `order_code` — `DATA-2`, `G-2`) | Mengubah `'total' => $total` menjadi `'total' => (int) $request->input('total', $total)` di `app/Http/Controllers/CheckoutController.php:138` | **YES** (`exit 1`) | `CheckoutTest::test_ac6_checkout_calculates_price_purely_on_server_generates_order_code_saves_snapshots_and_clears_cart` |
| **AC-7** (Simulasi bayar `qris_dummy` hanya untuk `qris_dummy` yang `pending` & `unpaid` — `DATA-1`) | Menghapus pengecekan `$locked->payment_method !== self::METHOD_QRIS_DUMMY` di `app/Models/Order.php:81` | **YES** (`exit 1`) | `CustomerOrderTest::test_ac7_qris_dummy_simulation_display_and_payment_rules` |
| **AC-8** (Larangan pembatalan pesanan yang sudah `paid` — `ARCH-1`, `DATA-1`, `G-2`) | Mengubah syarat `cancelByCustomer()` dari `$locked->status !== self::STATUS_PENDING || $locked->payment_status !== self::PAYMENT_UNPAID` menjadi hanya `$locked->status !== self::STATUS_PENDING` di `app/Models/Order.php:105` | **YES** (`exit 1`) | `CustomerOrderTest::test_ac8_customer_order_cancellation_rules_and_payment_vs_cancel_mutual_exclusion` |
| **AC-9** (Isolasi pesanan antar-pelanggan melalui `OrderPolicy` — `SEC-1`, `G-3`) | Mengubah `OrderPolicy::view()` agar mengembalikan `true` di `app/Policies/OrderPolicy.php:12` | **YES** (`exit 1`) | `CustomerOrderTest::test_ac9_order_policy_isolates_orders_between_customers` |
| **AC-10** (Proteksi rute `/admin` melalui `EnsureUserIsAdmin` — `SEC-1`, `G-3`) | Mengubah `if (! $request->user()->isAdmin())` menjadi `if (false)` di `app/Http/Middleware/EnsureUserIsAdmin.php:17` | **YES** (`exit 1`) | `AdminAccessTest::test_ac10_ensure_user_is_admin_middleware_protects_admin_routes` |
| **ARCH-1 / DATA-1** (Syarat `payment_status === 'paid'` untuk transisi `ready -> completed`) | Menghapus `&& $locked->payment_status === self::PAYMENT_PAID` pada `Order::transitionStatusByAdmin()` di `app/Models/Order.php:161` | **YES** (`exit 1`) | `CustomerOrderTest::test_centralized_order_state_machine_methods_enforce_admin_payment_and_transition_matrix` |

### 3. Product Usage & User Journey Verification
Aplikasi diuji dengan data `migrate:fresh --seed` (7 menu termasuk `Kopi Susu Gumi` Rp 24.000 `is_available = true` dan `Cold Brew Citrus` `is_available = false`, akun `customer@gumicoffee.test` dan `admin@gumicoffee.test`):
- **Halaman Katalog (`/menus`) & Keranjang (`/cart`)**: Menu dengan `is_available = true` merender form tambah keranjang (`POST /cart/items`) beserta input kuantitas `1..99` berlabel `<label for="qty-menu-...">`, sedangkan menu `is_available = false` merender tombol nonaktif `"Tidak Tersedia"` tanpa form. Indikator jumlah item keranjang pada navbar (`resources/views/layouts/navigation.blade.php:5,51`) memperbarui total kuantitas secara akurat.
- **Alur Tamu Login Menuju Checkout (`/checkout`)**: Pengunjung tamu yang mengakses `GET /checkout` diarahkan ke `/login`; setelah login, isi keranjang session tetap utuh dan pengguna langsung diarahkan ke `/checkout`.
- **Halaman Checkout & Detail Pesanan (`/orders/{order}`)**: Halaman `GET /checkout` menampilkan informasi **Takeaway / Pick-up di kasir**, teks estimasi `Estimasi siap 10-15 menit`, pilihan radio `cash` dan `qris_dummy`, serta textarea `notes` (`maxlength="500"`). Setelah `POST /checkout` dengan `qris_dummy`, halaman `GET /orders/{order}` menampilkan kode `GM-0001`, ilustrasi SVG QRIS dengan banner tegas **"SIMULASI"** (`resources/views/orders/show.blade.php:163,209`), tombol **"Saya sudah bayar"**, dan tombol **"Batalkan Pesanan"** (yang otomatis disembunyikan begitu pesanan dibayar atau dibatalkan).

---

## Review Dimensions

### 1. RFC ADHERENCE
- **Verdict**: PASS
- **Findings**:
  - Seluruh 10 kriteria penerimaan (`AC-1` s/d `AC-10`) dan seluruh 11 rute pada Section 3 `RFC-002` telah diimplementasikan sesuai spesifikasi tanpa penyimpangan.
  - Keputusan implementasi pada Section 4 (*Implementer's Choices*) telah dicatat dengan lengkap pada `## Implementation Notes` di `RFCs/RFC-002-Cart-Checkout-Customer-Orders.md:170-206`.
  - Keempat metode state machine terpusat pada `App\Models\Order` (`payQrisByCustomer()`, `cancelByCustomer()`, `markCashPaidByAdmin()`, `transitionStatusByAdmin()`) beserta konstanta status dan pembayarannya (`app/Models/Order.php:16-32,75-180`) telah siap digunakan oleh `RFC-003`.
- **Suggestions**: Tidak ada.

### 2. RULES COMPLIANCE
- **Verdict**: PASS
- **Findings**:
  - **ARCH-1 & DATA-1**: Seluruh mutasi status pesanan dan pembayaran dipusatkan di `app/Models/Order.php:75-180`, menggunakan `DB::transaction()` dan `self::whereKey($this->id)->lockForUpdate()->firstOrFail()`, memvalidasi ulang syarat setelah *lock*, serta melempar `ValidationException` dengan kunci deterministik (`payment_status` atau `status`). Tidak ada logika syarat transisi yang bocor ke `CustomerOrderController` (`app/Http/Controllers/CustomerOrderController.php:39-55`).
  - **ARCH-2**: Validasi jam operasional pada `app/Http/Controllers/CheckoutController.php:72-80` menggunakan `now('Asia/Jakarta')->format('H:i')` dan membaca `config('gumi.operating_hours.open')` serta `config('gumi.operating_hours.close')`.
  - **DATA-2 & DATA-3**: Transaksi checkout pada `app/Http/Controllers/CheckoutController.php:82-154` mengunci baris menu yang dibeli (`Menu::query()->whereIn('id', $menuIds)->lockForUpdate()->get()->keyBy('id')`), memvalidasi ulang `is_available`, menghitung `unit_price`, `subtotal`, dan `total` murni dari `$menu->price`, membuat `Order` dengan `order_code = null` lalu memperbaruinya menjadi `'GM-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)` di dalam transaksi yang sama, serta menyimpan snapshot `menu_name`, `quantity`, `unit_price`, dan `subtotal` ke `order_items`.
  - **SEC-1 & SEC-3**: Middleware `EnsureUserIsAdmin` terdaftar sebagai alias `'admin'` di `bootstrap/app.php:15-17` dan melindungi `GET /admin` (`routes/web.php:46-50`). `OrderPolicy` (`app/Policies/OrderPolicy.php:9-24`) melindungi `orders.show`, `orders.pay`, dan `orders.cancel` (`routes/web.php:35-43`). Seluruh form mutasi menggunakan `@csrf` dan validasi server-side (`quantity` `integer|min:1|max:99`, `notes` `nullable|string|max:500`).
  - **UI-1 & UI-2**: Seluruh view baru (`cart/index.blade.php`, `checkout/index.blade.php`, `orders/index.blade.php`, `orders/show.blade.php`, `admin/dashboard.blade.php`) menerapkan palet Playful Pop-Editorial (`#b22e3b`, `#fffaee`, `#e9bfd2`, `#020202`), tepat satu `<h1>` per halaman, elemen `<label>` untuk setiap input, tanpa tautan mati (`href="#"`), serta menampilkan label **"SIMULASI"** secara jelas pada tampilan `qris_dummy`.
  - **TEST-1**: Seluruh 12 pengujian otomatis yang direncanakan untuk `RFC-002` di `TEST-STRATEGY.md` telah diimplementasikan pada `CartTest.php`, `CheckoutTest.php`, `CustomerOrderTest.php`, dan `AdminAccessTest.php`, serta lulus 100%.
- **Suggestions**: Tidak ada.

### 3. SECURITY
- **Verdict**: PASS
- **Findings**:
  - **Server-Side Price Integrity**: `CheckoutController::store` (`app/Http/Controllers/CheckoutController.php:122-144`) mengabaikan seluruh parameter harga atau status dari request klien dan menghitung total murni dari database di dalam transaksi.
  - **Authorization & IDOR Prevention**: `OrderPolicy` (`app/Policies/OrderPolicy.php:11-23`) menggunakan perbandingan ketat `$user->id === $order->user_id` pada `view`, `pay`, dan `cancel`.
  - **Input & Route Parameter Validation**: Rute `PATCH /cart/items/{menuId}` dan `DELETE /cart/items/{menuId}` dibatasi dengan `->whereNumber('menuId')` (`routes/web.php:17,20`), dan `CheckoutController::store` juga memvalidasi ulang bahwa kuantitas item di session berada dalam rentang `1..99` (`app/Http/Controllers/CheckoutController.php:115-120`).
- **Suggestions**: Tidak ada.

### 4. PERFORMANCE
- **Verdict**: PASS
- **Findings**:
  - `CartController::index` (`app/Http/Controllers/CartController.php:18-21`), `CheckoutController::index` (`app/Http/Controllers/CheckoutController.php:24-27`), dan `CheckoutController::store` (`app/Http/Controllers/CheckoutController.php:93-97`) semuanya mengambil data menu keranjang dalam satu query `whereIn('id', ...)` yang diindeks dengan `keyBy('id')` (tidak ada query N+1 di dalam loop item keranjang).
  - `CustomerOrderController::index` (`app/Http/Controllers/CustomerOrderController.php:14-18`) melakukan *eager loading* `->with('items')` sehingga daftar riwayat pesanan pelanggan merender ringkasan item tanpa N+1 query.
- **Suggestions**: Tidak ada.

### 5. MAINTAINABILITY
- **Verdict**: PASS
- **Findings**:
  - Pemisahan tanggung jawab antara `CartController`, `CheckoutController`, `CustomerOrderController`, `OrderPolicy`, dan state machine pada model `Order` sangat bersih dan mudah diikuti.
  - *Temuan non-blocking*: Pada `resources/views/checkout/index.blade.php:140-154`, daftar ringkasan item di kolom kanan menampilkan `$item['subtotal']` (`$unitPrice * $quantity`) untuk setiap item di keranjang tanpa menampilkan badge peringatan jika `$item['is_available']` bernilai `false` (misalnya ketika pelanggan diarahkan kembali ke `GET /checkout` setelah `POST /checkout` gagal akibat `AC-5`, di mana `$total` di `app/Http/Controllers/CheckoutController.php:39-41` hanya menjumlahkan item yang `is_available = true`).
- **Suggestions**:
  - Pada `resources/views/checkout/index.blade.php:140-154`, tampilkan badge `"Tidak Tersedia"` di samping nama item ketika `!$item['is_available']` (seperti yang sudah dilakukan dengan baik pada `resources/views/cart/index.blade.php:54-58`) agar rincian item di halaman Checkout langsung menunjukkan item mana yang perlu dihapus jika status ketersediaannya berubah.

---

## Overall Assessment

**Overall verdict**: PASS

- **Overall Risk Level**: Low
- **Summary**: Implementasi `RFC-002` memenuhi seluruh 10 kriteria penerimaan (`AC-1` s/d `AC-10`), mematuhi seluruh aturan `RULES.md` (`ARCH-1`, `ARCH-2`, `DATA-1`, `DATA-2`, `DATA-3`, `SEC-1`, `SEC-3`, `UI-1`, `UI-2`, `TEST-1`), serta menyediakan seluruh 12 pengujian otomatis yang direncanakan pada `TEST-STRATEGY.md`. Seluruh 44 test (345 assertion) lulus 100% dan terbukti menangkap regresi pada setiap kriteria melalui pengujian *fault-injection*.
- **Blocking Issues**: None.
- **Improvement Suggestions**:
  1. **(RFC-002)** Tampilkan badge `"Tidak Tersedia"` pada daftar ringkasan item di `resources/views/checkout/index.blade.php:140-154` apabila `!$item['is_available']` (selaras dengan `resources/views/cart/index.blade.php:54-58`), sehingga ketika checkout ditolak karena perubahan mendadak status menu (`AC-5`), pelanggan dapat langsung melihat indikator pada baris item terkait di kartu ringkasan Checkout.
