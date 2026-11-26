# Code Review: RFC-001 — Project Foundation, Authentication, Schema & Public Catalog

- **RFC**: `RFCs/RFC-001-Foundation-Auth-Catalog.md`
- **Features Covered**: `F1`, `F2`, `F3`, `F4`
- **Rules Checked**: `ARCH-2`, `DATA-3`, `SEC-2`, `UI-1`, `UI-2`, `TEST-1`
- **Product Type & Size**: `web app`, Size: `Small` (mengikuti `## Product Type` pada `PRD.md`; tidak ada pengecekan tambahan yang dilewati di luar `binary size` dan `offline sync` yang sudah tercatat di `PRD.md`).
- **Branch Reviewed**: `feat/rfc-001-foundation-auth-catalog`

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
public/build/assets/app-swGlW5n4.css  58.94 kB │ gzip: 10.27 kB
public/build/assets/app-Dr67-uCa.js   54.32 kB │ gzip: 19.13 kB

✓ built in 1.59s
```

**Automated Test Suite (`php vendor/bin/phpunit --testdox`)**:
```text
PHPUnit 12.5.45 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.12
Configuration: D:\Projects\gumi-coffee\phpunit.xml

Time: 00:03.131, Memory: 64.00 MB

Authentication (Tests\Feature\Auth\Authentication)
 ✔ Login screen can be rendered
 ✔ Users can authenticate using the login screen and redirect to menus by default
 ✔ Dashboard route redirects authenticated users to menus
 ✔ Users can not authenticate with invalid password
 ✔ Users can logout

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

OK (32 tests, 168 assertions)
```

### 2. Fault Injection Verification (`AC-1` s/d `AC-10`)

Setiap kriteria penerimaan dan properti kritis (`G-3`, `ARCH-2`, `DATA-3`, `SEC-2`) telah dirusak secara sengaja pada kode implementasi, diuji terhadap PHPUnit untuk memastikan pengujian menangkap regresi tersebut (**FAIL**), lalu dikembalikan ke kondisi semula:

| Criterion / Property | What Was Broken (Fault Injected) | Caught by Test? | Failing Test & Assertion |
|---|---|---|---|
| **AC-1a** (Timezone `Asia/Jakarta`) | Mengubah `'timezone' => 'Asia/Jakarta'` menjadi `'UTC'` di `config/app.php:68` | **YES** (`exit 1`) | `FoundationAndCatalogTest::test_ac1_timezone_is_asia_jakarta_and_homepage_reads_central_operating_hours_config` (`Failed asserting that 'UTC' is identical to 'Asia/Jakarta'`) |
| **AC-1b** (Jam buka dari `config('gumi.operating_hours')`) | Mengganti pembacaan jam operasional di template `GET /` dengan string *hardcoded* `'08:00'` | **YES** (`exit 1`) | `FoundationAndCatalogTest::test_ac1_timezone_is_asia_jakarta_and_homepage_reads_central_operating_hours_config` (`Failed asserting that HTML contains '09:00'`) |
| **AC-2** (Beranda & CTA `"Lihat Menu"`) | Mengubah teks tautan CTA `"Lihat Menu"` pada `GET /` menjadi `"Jelajahi"` | **YES** (`exit 1`) | `FoundationAndCatalogTest::test_ac2_homepage_displays_brand_story_location_and_lihat_menu_cta_to_menus` (`Failed asserting that HTML contains 'Lihat Menu'`) |
| **AC-3a** (`orders.user_id` `restrictOnDelete()` — Anti-kehilangan riwayat `G-3`) | Mengubah `->restrictOnDelete()` menjadi `->cascadeOnDelete()` di `0001_01_01_000004_create_orders_table.php:17` | **YES** (`exit 1`) | `DatabaseIntegrityTest::test_ac3_schema_excludes_out_of_scope_columns_and_enforces_relations_and_foreign_key_constraints` (`Failed asserting that exception of type QueryException is thrown`) |
| **AC-3b** (`order_items.menu_id` `nullOnDelete()` — Snapshot riwayat item `G-3`) | Mengubah `->nullOnDelete()` menjadi `->cascadeOnDelete()` di `0001_01_01_000005_create_order_items_table.php:17` | **YES** (`exit 2`) | `DatabaseIntegrityTest::test_ac3_schema_excludes_out_of_scope_columns_and_enforces_relations_and_foreign_key_constraints` (`ModelNotFoundException` saat `$orderItem->refresh()`) |
| **AC-4** (`DatabaseSeeder` akun demo & aset gambar statis) | Mengubah `'role' => 'admin'` menjadi `'customer'` untuk `admin@gumicoffee.test` di `DatabaseSeeder.php:22` | **YES** (`exit 1`) | `DatabaseSeederTest::test_ac4_database_seeder_creates_demo_accounts_and_menus_with_existing_static_images` (`Failed asserting that 'customer' is identical to 'admin'`) |
| **AC-5** (Katalog publik & *fallback placeholder* gambar) | Merusak atribut `data-testid="menu-image-placeholder"` pada blok fallback `resources/views/menus/index.blade.php:58` | **YES** (`exit 1`) | `FoundationAndCatalogTest::test_ac5_public_catalog_is_accessible_by_guest_and_renders_categories_rupiah_prices_and_image_fallback` |
| **AC-6** (Badge menu `is_available = false`) | Mengubah label `"Habis / Tidak Tersedia"` menjadi `"Masih Ada"` di `resources/views/menus/index.blade.php:80` | **YES** (`exit 1`) | `FoundationAndCatalogTest::test_ac6_unavailable_menu_displays_unavailable_badge_and_has_no_active_order_control` |
| **AC-7** (Registrasi publik memaksa `role = 'customer'` — `SEC-2`) | Mengubah `'role' => 'customer'` menjadi `'role' => $request->input('role', 'customer')` di `RegisteredUserController.php:43` | **YES** (`exit 1`) | `RegistrationTest::test_new_users_can_register_and_are_forced_to_customer_role_even_if_admin_role_is_injected` (`Failed asserting that 'admin' is identical to 'customer'`) |
| **AC-8** (Login redirect default ke `/menus`) | Mengubah redirect default login dari `route('menus.index', absolute: false)` menjadi `'/'` di `AuthenticatedSessionController.php:31` | **YES** (`exit 1`) | `AuthenticationTest::test_users_can_authenticate_using_the_login_screen_and_redirect_to_menus_by_default` |
| **AC-9** (Penonaktifan hapus akun `DELETE /profile` — `SEC-2`) | Menambahkan kembali rute `Route::delete('/profile', fn() => response('ok', 200))` di `routes/web.php` | **YES** (`exit 1`) | `ProfileTest::test_delete_profile_endpoint_is_disabled_and_does_not_delete_user` (`Failed asserting that [404, 405] contains 200`) |
| **AC-10** (Semantik HTML, satu `<h1>`, & palet Playful Pop-Editorial) | Menambahkan elemen `<h1>Extra</h1>` kedua di `resources/views/home.blade.php:13` | **YES** (`exit 1`) | `FoundationAndCatalogTest::test_ac10_public_pages_use_playful_pop_editorial_palette_sections_semantic_landmarks_and_single_h1` (`Expected exactly one <h1> tag on /`) |

### 3. Product Usage & Screen Verification
Aplikasi dijalankan dengan database yang di-*seed* melalui `migrate:fresh --seed`:
- `GET /` (`200 OK`): Menampilkan 8 pita visual Playful Pop-Editorial (`CR-001`), jam buka `08:00 - 22:00 WIB`, lokasi `Jl. Braga No. 42, Bandung`, estimasi `Estimasi siap 10-15 menit`, 3 menu unggulan, serta tombol filter kategori (`#kategori-coffee`, `#kategori-non-coffee`, `#kategori-pastry`) menuju `/menus`.
- `GET /menus` (`200 OK`): Menampilkan 7 menu dari `DatabaseSeeder` yang terkelompok rapi ke dalam `Coffee` (4 menu), `Non-Coffee` (2 menu), dan `Pastry` (1 menu); `Cold Brew Citrus` (`is_available = false`) menampilkan badge `"Habis / Tidak Tersedia"` dan `"Stok Habis"`.
- `GET /login` & `GET /register` (`200 OK`): Menggunakan tata letak Playful Pop-Editorial berbahasa Indonesia dengan label form eksplisit dan tombol `.pop-btn-crimson`.

---

## Review Dimensions

### 1. RFC ADHERENCE
- **Verdict**: PASS
- **Findings**:
  - Seluruh 10 kriteria penerimaan (`AC-1` s/d `AC-10`) pada `RFC-001` terpenuhi sepenuhnya tanpa penyimpangan yang tidak tercatat dan tanpa penambahan fitur di luar cakupan.
  - Seluruh kontrak antarmuka untuk RFC hilir (`RFC-002` dan `RFC-003`) telah tersedia sesuai spesifikasi Section 3: model `User` (`isAdmin()`, `orders()`), `Menu` (`orderItems()`, cast `price` & `is_available`), `Order` (`user()`, `items()`, cast `total` & `paid_at`), `OrderItem` (`order()`, `menu()`, cast integer), serta keempat kunci konfigurasi di `config/gumi.php`.
- **Suggestions**: Tidak ada.

### 2. RULES COMPLIANCE
- **Verdict**: PASS
- **Findings**:
  - **ARCH-2**: `config/app.php:68` menetapkan `'timezone' => 'Asia/Jakarta'`, dan seluruh tampilan jam operasional membaca dari `config('gumi.operating_hours')`.
  - **DATA-3**: `database/migrations/0001_01_01_000004_create_orders_table.php:17` menggunakan `restrictOnDelete()` pada `user_id` dan tidak menyertakan kolom `order_type` maupun `table_id`; `database/migrations/0001_01_01_000005_create_order_items_table.php:17-21` menggunakan `nullable()->constrained('menus')->nullOnDelete()` pada `menu_id` serta menyimpan kolom snapshot `menu_name`, `quantity`, `unit_price`, dan `subtotal`.
  - **SEC-2**: Rute `DELETE /profile`, metode `ProfileController::destroy`, dan partial `delete-user-form.blade.php` telah dihapus, serta `RegisteredUserController::store` (`app/Http/Controllers/Auth/RegisteredUserController.php:43`) menetapkan `'role' => 'customer'` secara eksplisit.
  - **UI-1 & UI-2**: Halaman publik (`resources/views/home.blade.php`, `resources/views/menus/index.blade.php`) dan halaman autentikasi (`resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`) menerapkan palet warna `#b22e3b`, `#fffaee`, `#e9bfd2`, dan `#020202` dengan kontras WCAG AA, struktur HTML semantik (`header`, `nav`, `main`, `footer`, satu `h1`), dan tanpa tautan mati (`href="#"`).
  - *Temuan non-blocking*: Halaman `resources/views/profile/edit.blade.php` sudah menerapkan pembungkus Playful Pop-Editorial, namun dua partial bawaan Breeze yang di-*include* di dalamnya (`resources/views/profile/partials/update-profile-information-form.blade.php:3-59` dan `resources/views/profile/partials/update-password-form.blade.php:3-44`) beserta komponen input/tombolnya (`resources/views/components/text-input.blade.php:3`, `resources/views/components/primary-button.blade.php:1`) masih menggunakan kelas warna abu-abu/indigo bawaan Breeze (`text-gray-900`, `bg-gray-800`, `focus:ring-indigo-500`) dan teks bahasa Inggris (`Profile Information`, `Save`, `Update Password`).
- **Suggestions**:
  - Selaraskan kelas styling dan teks label pada `resources/views/profile/partials/update-profile-information-form.blade.php` serta `resources/views/profile/partials/update-password-form.blade.php` agar menggunakan `.pop-input`, `.pop-btn-crimson`, palet `#020202`/`#fffaee`/`#b22e3b`, dan salinan bahasa Indonesia seperti pada `login.blade.php` dan `register.blade.php`.

### 3. SECURITY
- **Verdict**: PASS
- **Findings**:
  - Pendaftaran pengguna baru di `app/Http/Controllers/Auth/RegisteredUserController.php:43` secara eksplisit menetapkan `'role' => 'customer'` dan mengabaikan input `role` dari request publik.
  - Pembaruan profil di `app/Http/Controllers/ProfileController.php:28` hanya meneruskan `$request->validated()` dari `ProfileUpdateRequest` (`name` dan `email`), sehingga meskipun `'role'` terdaftar di `User::$fillable` (`app/Models/User.php:23` sesuai spesifikasi `RFC-001` Section 3), pengguna tidak dapat mengubah `role` lewat endpoint profil.
  - Seluruh form mutasi (`POST /login`, `POST /register`, `POST /logout`, `PATCH /profile`, `PUT /password`) dilengkapi proteksi `@csrf`.
- **Suggestions**:
  - Pastikan setiap controller di RFC berikutnya tetap hanya menggunakan `$request->validated()` saat memanggil `User::fill()` atau `User::update()` karena `'role'` berada di dalam `User::$fillable`.

### 4. PERFORMANCE
- **Verdict**: PASS
- **Findings**:
  - `HomeController::index` (`app/Http/Controllers/HomeController.php:12-16`) membatasi query menu unggulan hanya 3 baris (`where('is_available', true)->orderBy('id')->limit(3)->get()`).
  - `MenuController::index` (`app/Http/Controllers/MenuController.php:13-15`) mengambil daftar katalog dalam 1 query tanpa masalah N+1.
  - Ketujuh aset ilustrasi menu berformat SVG statis di `public/images/menus/` berukuran sangat ringan (~1,3 KB per file) dan dilengkapi atribut dimensi eksplisit (`width="400" height="320"`), `fetchpriority="high"` pada gambar hero, serta `loading="lazy"` pada daftar kartu menu.
- **Suggestions**: Tidak ada.

### 5. MAINTAINABILITY
- **Verdict**: PASS
- **Findings**:
  - Struktur kode bersih, terorganisasi sesuai konvensi Laravel, dan seluruh 11 pengujian terencana untuk `RFC-001` di `TEST-STRATEGY.md` telah diimplementasikan (total 32 test, 168 assertion lulus 100%).
  - *Temuan non-blocking dari Fault Injection*: Pada `tests/Feature/FoundationAndCatalogTest.php:25-26` (`test_ac1`) dan `tests/Feature/FoundationAndCatalogTest.php:36-39` (`test_ac2`), assertion `$response->assertSeeText('09:00')`, `$response->assertSeeText('21:30')`, `$response->assertSeeText('Gumi Coffee')`, dan `$response->assertSeeText('Lihat Menu')` memeriksa keseluruhan dokumen HTML. Karena `layouts/navigation.blade.php` dan footer `layouts/app.blade.php` juga menampilkan `config('gumi.operating_hours')`, `config('gumi.location')`, dan tautan `"Lihat Menu"`, regresi yang hanya terjadi di dalam `<main>` pada `resources/views/home.blade.php` atau `HomeController` dapat tertutup oleh teks di header/footer layout.
- **Suggestions**:
  - Perketat `test_ac1` dan `test_ac2` di `tests/Feature/FoundationAndCatalogTest.php` agar juga memverifikasi teks spesifik yang hanya ada di dalam `<main>` `resources/views/home.blade.php` (misalnya `assertSeeText('Cerita singkat Gumi Coffee')` dan memastikan jam operasional tampil pada blok `<main>`).

---

## Overall Assessment

**Overall verdict**: PASS

- **Overall Risk Level**: Low
- **Summary**: Implementasi `RFC-001` memenuhi seluruh 10 kriteria penerimaan (`AC-1` s/d `AC-10`), mematuhi standar arsitektur, integritas skema database (`restrictOnDelete()` & `nullOnDelete()`), keamanan autentikasi (`SEC-2`), serta arah visual Playful Pop-Editorial (`CR-001`). Seluruh 32 pengujian otomatis (168 assertion) lulus 100% dan terbukti menangkap regresi melalui pengujian *fault-injection*.
- **Blocking Issues**: None.
- **Improvement Suggestions**:
  1. **(RFC-001)** Selaraskan kelas styling dan bahasa label pada partial form profil `resources/views/profile/partials/update-profile-information-form.blade.php` serta `resources/views/profile/partials/update-password-form.blade.php` dengan komponen `.pop-input` dan `.pop-btn-crimson` berbahasa Indonesia agar seragam dengan halaman `login` dan `register`.
  2. **(RFC-001)** Perketat assertion `test_ac1` dan `test_ac2` pada `tests/Feature/FoundationAndCatalogTest.php` agar memverifikasi konten spesifik di dalam `<main>` `resources/views/home.blade.php` (seperti `'Cerita singkat Gumi Coffee'`) sehingga tidak sekadar cocok dengan teks pada navbar/footer layout.
