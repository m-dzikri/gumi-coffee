# Project Rules & Engineering Standards: Gumi Coffee

## Project Overview

**Gumi Coffee** adalah aplikasi web profil brand ringan dan pemesanan kopi *takeaway / pick-up* berbasis **Laravel** (`web app`, skala `Small`). Dokumen `RULES.md` ini menetapkan standar arsitektur, integritas data, keamanan, antarmuka pengguna, pengujian, dan kompatibilitas runtime yang **wajib** dipatuhi oleh setiap RFC (`/implement-rfc`) dan diperiksa satu per satu pada tahap tinjauan kode (`/review-rfc`).

Sesuai klasifikasi `## Product Type` pada `PRD.md` (`web app`, `Small`), pengecekan `binary size` dan `offline sync` dilewati (tidak ada pengecekan tambahan yang dilewati di luar daftar `PRD.md`).

---

## Technology Stack (Registry-Verified Versions)

Seluruh versi teknologi di bawah ini telah diverifikasi terhadap lingkungan lokal serta registri resmi (**Packagist** dan **npm**) pada saat penyusunan aturan ini:

| Komponen | Paket / Runtime | Versi Terverifikasi | Sumber Verifikasi | Peran dalam Proyek |
|---|---|---|---|---|
| **Bahasa & Runtime Backend** | `php` | `8.5.12` (kompatibel `^8.2`) | Local CLI (`php -v`) | Eksekusi aplikasi Laravel |
| **Package Manager PHP** | `composer` | `2.10.3` | Local CLI (`composer --version`) | Manajemen dependensi PHP |
| **Framework Web** | `laravel/framework` | `v13.44.1` (atau `^12.0` / `^13.0`) | Packagist Registry | Kerangka kerja utama MVC, routing, ORM, session, dan transaksi |
| **Auth Scaffolding** | `laravel/breeze` | `v2.4.2` (`^2.4`) | Packagist Registry | Autentikasi sesi & profil berbasis Blade + Tailwind + Alpine |
| **Testing Framework** | `phpunit/phpunit` | `13.4.8` (atau versi bawaan Laravel) | Packagist Registry | Eksekusi *Automated Feature Test* & *Unit Test* |
| **Runtime Frontend Build** | `node` | `v24.20.0` | Local CLI (`node -v`) | Build aset CSS & JS |
| **CSS Framework** | `tailwindcss` | `4.3.5` (atau `^3.4` / `^4.3` via Breeze) | npm Registry | Styling antarmuka **Urban / Neo-Brutalist** responsif |
| **Frontend Interactivity** | `alpinejs` | `3.17.5` (`^3.17`) | npm Registry | Interaktivitas ringan di sisi klien (dropdown, modal, toggle) |
| **Asset Bundler** | `vite` | `8.3.8` | npm Registry | Bundling aset statis frontend |
| **Database** | SQLite (Lokal/Test) & PostgreSQL/MySQL (Produksi) | Native PDO | Laravel Database Driver | Penyimpanan relasional (`users`, `menus`, `orders`, `order_items`) |

---

## Rule Summary

| Prefix | Kategori | Jumlah Aturan | ID Aturan |
|---|---|---|---|
| **ARCH** | Architecture & State Management | 2 | `ARCH-1`, `ARCH-2` |
| **DATA** | Database, Transactions & Data Integrity | 3 | `DATA-1`, `DATA-2`, `DATA-3` |
| **SEC** | Security & Authorization | 3 | `SEC-1`, `SEC-2`, `SEC-3` |
| **UI** | UI, Responsiveness & Accessibility | 2 | `UI-1`, `UI-2` |
| **TEST** | Testing & Completeness Standards | 1 | `TEST-1` |
| **OPS** | Deployment & Serverless Runtime Portability | 1 | `OPS-1` |
| **Total** | **Seluruh Kategori** | **12** | `ARCH-1` s/d `OPS-1` |

---

## Mandatory Engineering Rules

### 1. Architecture & State Management (`ARCH`)

- **ARCH-1**: Seluruh aturan transisi status pesanan (`pending`, `preparing`, `ready`, `completed`, `cancelled`) beserta prasyarat metode dan status pembayarannya (`cash`/`qris_dummy`, `unpaid`/`paid`) wajib dipusatkan di satu tempat (metode khusus pada model `Order` atau class Enum/Service domain pesanan); dilarang menulis logika percabangan syarat transisi status secara tersebar di dalam Controller atau Blade view. *(Memenuhi NFR-6).*
- **ARCH-2**: Konfigurasi `config/app.php` wajib menetapkan `'timezone' => 'Asia/Jakarta'`, dan seluruh tampilan maupun validasi jam operasional kedai wajib membaca nilai dari satu konfigurasi terpusat `config('gumi.operating_hours')` (dilarang menulis jam buka secara *hardcoded* di Controller atau Blade view). *(Memenuhi NFR-2).*

### 2. Database, Transactions & Data Integrity (`DATA`)

- **DATA-1**: Setiap aksi mutasi yang mengubah `orders.status`, `orders.payment_status`, atau `orders.paid_at` (pembayaran `qris_dummy` oleh pelanggan, penandaan lunas `cash` oleh admin, perubahan status pesanan oleh admin, maupun pembatalan pesanan oleh pelanggan/admin) wajib dijalankan di dalam `DB::transaction()` yang mengunci baris pesanan dengan `Order::whereKey($id)->lockForUpdate()->firstOrFail()` dan memvalidasi ulang syarat `status` serta `payment_status` setelah *lock* diperoleh. *(Memenuhi NFR-1).*
- **DATA-2**: Eksekusi *checkout* wajib berjalan di dalam satu `DB::transaction()` yang mengecek ulang `menus.is_available = true` di database, menghitung `unit_price`, `subtotal`, dan `total` murni dari `menus.price` di server (dilarang menggunakan nilai harga dari *request* klien), melakukan *insert* awal ke `orders` dengan `order_code = null`, lalu segera memperbarui `order_code` (`string(20)->nullable()->unique()`) dengan format `'GM-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)` di dalam transaksi yang sama sebelum *commit*. *(Memenuhi NFR-3).*
- **DATA-3**: Skema migrasi database wajib menetapkan `orders.user_id` dengan `restrictOnDelete()`, menetapkan `order_items.menu_id` sebagai `nullable()->constrained('menus')->nullOnDelete()`, menyimpan salinan kolom `menu_name`, `unit_price`, dan `subtotal` pada tabel `order_items`, serta dilarang menambahkan kolom di luar cakupan awal seperti `order_type` atau `table_id` pada tabel `orders`. *(Memenuhi NFR-3, NFR-6).*

### 3. Security & Authorization (`SEC`)

- **SEC-1**: Seluruh rute di bawah prefix `/admin` wajib dilindungi oleh middleware `auth` dan `EnsureUserIsAdmin` (yang mengembalikan HTTP `403 Forbidden` bagi pengguna ber-role `customer`), sedangkan seluruh rute pelanggan yang mengakses detail pesanan, membayar `qris_dummy`, atau membatalkan pesanan wajib diotorisasi melalui `OrderPolicy` (`$user->id === $order->user_id`). *(Memenuhi NFR-3).*
- **SEC-2**: Rute `DELETE /profile`, metode `ProfileController::destroy`, dan bagian UI penghapusan akun bawaan Laravel Breeze wajib dihapus, serta proses registrasi publik wajib menetapkan `users.role = 'customer'` secara eksplisit di sisi server (kolom `role` dilarang diisi melalui *mass-assignment* dari input form publik). *(Memenuhi NFR-3).*
- **SEC-3**: Seluruh endpoint mutasi (`POST`, `PUT`, `PATCH`, `DELETE`) wajib menggunakan proteksi CSRF (`@csrf`) dan memvalidasi input di sisi server dengan batasan tegas: `quantity` keranjang berupa bilangan bulat `integer|min:1|max:99`, `orders.notes` berupa `nullable|string|max:500`, `menus.price` berupa bilangan bulat `integer|min:1000`, serta `menus.slug` unik. *(Memenuhi NFR-3).*

### 4. UI, Responsiveness & Accessibility (`UI`)

- **UI-1**: Antarmuka wajib mengimplementasikan tema **Urban / Neo-Brutalist** menggunakan palet warna `#b22e3b` (utama), `#fffaee` (latar terang), `#e9bfd2` (aksen), dan `#020202` (teks, *border* tegas, dan *hard offset shadow*) dengan kombinasi warna teks yang memenuhi standar kontras WCAG AA (`#020202` di atas `#fffaee`/`#e9bfd2`, dan `#fffaee` di atas `#b22e3b`), serta bebas dari *horizontal overflow* pada lebar layar `375px` (Mobile), `768px` (Tablet), dan `1280px+` (Desktop). *(Memenuhi NFR-4).*
- **UI-2**: Setiap elemen interaktif pada Blade view wajib berfungsi penuh tanpa tautan atau tombol mati (dilarang menyisakan `href="#"` tanpa aksi, komentar `// TODO`, atau komponen placeholder tanpa fungsi), wajib menggunakan struktur HTML semantik (`header`, `nav`, `main`, `footer`, satu `h1` per halaman, serta `<label>` untuk setiap input form), dan tampilan pembayaran `qris_dummy` wajib memuat label teks **"SIMULASI"** secara jelas. *(Memenuhi NFR-4).*

### 5. Testing & Completeness Standards (`TEST`)

- **TEST-1**: Setiap fitur wajib disertai *Feature Test* otomatis (PHPUnit dengan trait `RefreshDatabase`) yang lulus 100% dan mencakup: (a) ketahanan *session cart* tamu setelah login menuju *checkout*, (b) kalkulasi harga *checkout* di server beserta penolakan di luar jam operasional atau saat menu `is_available = false`, (c) penolakan akses `customer` ke `/admin/*` (`403`) dan ketiadaan rute hapus akun profil, (d) isolasi pesanan antar-pelanggan melalui `OrderPolicy` (`403`), serta (e) seluruh aturan positif dan negatif pada matriks transisi status, pembayaran (`cash` & `qris_dummy`), dan pembatalan pesanan (termasuk pembatalan pesanan `cash` berstatus `ready + unpaid` oleh admin dan penolakan pembatalan pesanan yang sudah `paid`). *(Memenuhi NFR-7).*

### 6. Deployment & Serverless Runtime Portability (`OPS`)

- **OPS-1**: Aplikasi wajib menyertakan konfigurasi serverless Vercel (`vercel.json` dan `api/index.php`) yang mengarahkan `VIEW_COMPILED_PATH`, `APP_CONFIG_CACHE`, dan `APP_ROUTES_CACHE` ke direktori `/tmp`, mendukung `LOG_CHANNEL=stderr` serta driver sesi non-file (`cookie` atau `database`), dan `DatabaseSeeder` wajib mereferensikan gambar menu dari aset statis di dalam direktori `public/` (dengan fallback placeholder jika `image_path` kosong) agar katalog demo selalu tampil di lingkungan *read-only filesystem*. *(Memenuhi NFR-5).*

---

## Open Questions

Tidak ada asumsi baru yang ditambahkan di `RULES.md`. Dua asumsi infrastruktur produksi Vercel (pemilihan layanan database relasional terkelola dan penyimpanan gambar upload baru oleh Admin di produksi) merujuk pada `OQ-1` dan `OQ-2` di dalam `PRD.md`.
