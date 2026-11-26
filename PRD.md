# Product Requirements Document (PRD): Gumi Coffee

## Overview

**Gumi Coffee** adalah aplikasi web profil brand ringan (*lightweight brand profile*) dan pemesanan kopi *takeaway / pick-up* berbasis **Laravel** yang dirancang sebagai proyek portofolio rekayasa perangkat lunak. Aplikasi ini memungkinkan pelanggan melihat katalog menu Gumi Coffee, memasukkan menu ke keranjang belanja, melakukan pemesanan untuk diambil di kasir (*pick-up*), serta melakukan simulasi pembayaran (`cash` atau `qris_dummy`). Di sisi operasional, Admin dapat mengelola ketersediaan dan data katalog menu serta memproses siklus status pesanan dan pembayaran secara terpusat dan aman dari *race condition*.

Aplikasi mengusung arah visual **Urban / Neo-Brutalist** yang tegas dan responsif di perangkat **Desktop, Tablet, dan Mobile**, dengan fokus utama pada keandalan logika transaksi bisnis, integritas data, otorisasi berbasis peran (*role-based access control*), dan pengujian otomatis (*Feature Test*).

---

## Product Type

- **Type**: `web app`
- **Size**: `Small` (dikerjakan mandiri oleh 1 developer dalam target beberapa minggu; dokumentasi arsitektur, daftar fitur, RFC, dan strategi pengujian dibuat ringkas serta langsung ke inti).

### Probed Areas (Web App)
- **Auth and sessions**: Autentikasi berbasis sesi menggunakan Laravel Breeze (tanpa fitur hapus akun), keranjang belanja (*cart*) berbasis sesi yang bertahan setelah tamu (*guest*) melakukan login menuju *checkout*.
- **Authorization per resource**: Pemisahan akses antara `customer` dan `admin` menggunakan middleware khusus (`EnsureUserIsAdmin`) untuk seluruh rute `/admin/*` serta `OrderPolicy` untuk memastikan pelanggan hanya dapat mengakses, membayar, atau membatalkan pesanannya sendiri.
- **Data model and migrations**: Skema relasional bersih (`users`, `menus`, `orders`, `order_items`) dengan *snapshot* nama serta harga menu saat transaksi, *foreign key constraint* yang menjaga riwayat pesanan, dan penguncian baris (`lockForUpdate()`) di dalam transaksi database.
- **Accessibility**: Kontras warna yang memenuhi standar keterbacaan WCAG AA pada palet warna Urban (`#b22e3b`, `#fffaee`, `#e9bfd2`, `#020202`), navigasi keyboard yang jelas, dan elemen interaktif semantik.
- **Responsive layout**: Tata letak responsif penuh untuk layar **Mobile**, **Tablet**, dan **Desktop**.
- **Browser support**: Mendukung peramban modern utama (Chrome, Firefox, Edge, Safari di desktop dan perangkat seluler).
- **Page-load budget**: Halaman dirender di server menggunakan Laravel Blade, Tailwind CSS, dan Alpine.js tanpa *bundle* SPA yang berat.
- **SEO for public pages**: Struktur HTML semantik (`header`, `nav`, `main`, `footer`, satu `h1`, metadata `<title>`) pada halaman publik (Beranda *Brand Profile* dan Katalog Menu).

### Skipped Checks
- **binary size**: Dilewati karena produk adalah aplikasi web berbasis *server-rendered* (Laravel Blade), bukan aplikasi *native* atau *binary* yang diunduh pengguna.
- **offline sync**: Dilewati karena pemesanan kopi *takeaway* membutuhkan validasi jam operasional kedai dan status ketersediaan menu (`is_available`) secara *real-time* di server.

---

## Goals and Objectives

- **G-1 (Kemudahan Pemesanan Takeaway)**: Menyediakan alur pemesanan kopi *takeaway* yang ringkas dari penjelajahan katalog hingga mendapatkan kode pesanan pendek (`GM-0001`) dengan estimasi waktu siap yang jelas. *(Dicapai melalui FR-1, FR-2, FR-4, FR-5, FR-6, FR-7, FR-8, FR-9).*
- **G-2 (Integritas Transaksi & State Machine)**: Menjamin perhitungan harga murni di sisi server, aturan transisi status pesanan dan pembayaran yang terkunci di satu tempat tanpa *deadlock* (termasuk penanganan pesanan *cash* yang tidak diambil), serta bebas dari *race condition*. *(Dicapai melalui FR-8, FR-10, FR-11, FR-14, FR-15, FR-16, NFR-1, NFR-2).*
- **G-3 (Keamanan Akses & Isolasi Data)**: Memastikan pemisahan hak akses yang ketat antara pelanggan dan admin, serta mencegah pelanggan melihat atau memanipulasi pesanan milik pelanggan lain. *(Dicapai melalui FR-3, FR-12, FR-13, NFR-3, NFR-7).*
- **G-4 (Kesiapan Demo Portofolio & Pengujian)**: Memudahkan *recruiter* atau penguji mencoba aplikasi secara langsung melalui data *seeder* siap pakai, tampilan responsif bergaya Urban di semua ukuran layar, kompatibilitas *deployment* Vercel, dan cakupan *Feature Test* pada seluruh aturan bisnis kritis. *(Dicapai melalui FR-17, NFR-4, NFR-5, NFR-6, NFR-7).*

---

## Scope

### In Scope (Rilis Awal)
1. **Halaman Beranda (Brand Profile Ringan)**: Cerita singkat Gumi Coffee, jam operasional (dari konfigurasi terpusat), lokasi kedai, dan tombol CTA menuju katalog menu.
2. **Katalog Menu Publik & Keranjang Session**: Penampilan daftar menu beserta status ketersediaan (`is_available`), pengelompokan kategori sederhana, dan penambahan item ke keranjang berbasis sesi (termasuk oleh tamu sebelum login).
3. **Autentikasi & User Profile Ringan (Laravel Breeze)**: Registrasi, login, logout, ubah profil (nama, email, password) **tanpa fitur hapus akun**, serta halaman riwayat pesanan milik pelanggan.
4. **Checkout Takeaway & Kode Pesanan Unik**: Validasi jam buka (`Asia/Jakarta`), pengecekan ulang `is_available`, kolom catatan pesanan (`notes`), teks estimasi waktu siap statis (misal: *"10-15 menit"*), perhitungan harga di server dalam satu transaksi DB, dan pembuatan `order_code` berformat `GM-0001` dari ID pesanan.
5. **Simulasi Pembayaran (Tanpa Payment Gateway)**:
   - Metode `cash`: Dibayar di kasir; ditandai lunas (`paid`) oleh Admin.
   - Metode `qris_dummy`: Menampilkan gambar QRIS simulasi berlabel **"SIMULASI"** dan tombol **"Saya sudah bayar"** untuk mengubah status menjadi `paid` oleh pemilik pesanan saat status pesanan masih `pending` dan `unpaid`.
6. **Manajemen Pesanan & Menu oleh Admin**: CRUD menu, *quick-toggle* `is_available`, daftar pesanan masuk dengan filter status, pencarian `order_code`, pembaruan status pesanan sesuai aturan transisi terpusat, penandaan lunas pesanan `cash`, dan pembatalan pesanan `unpaid`.
7. **Database Seeder & Feature Tests**: Akun demo (`admin` & `customer`), data menu contoh, serta pengujian otomatis untuk *checkout*, otorisasi, isolasi data pelanggan, dan validasi transisi status/pembayaran.

### Out of Scope / Deferred (Di Luar Cakupan Awal)
1. **Dine-in & Nomor Meja / Scan QR Meja**: Tidak ada kolom `order_type` atau `table_id` pada rilis awal (dirancang agar mudah ditambahkan lewat *migration* di masa depan).
2. **Delivery & E-Commerce Biji Kopi**: Tidak ada perhitungan ongkos kirim, alamat pengiriman, atau integrasi kurir ekspedisi.
3. **Varian Menu & Stok Angka**: Tidak ada opsi ukuran cup, level gula/es, *topping*, maupun pengurangan jumlah stok angka (hanya boolean `is_available`).
4. **Penjadwalan Jam Ambil**: Tidak ada pemilih jam ambil (*time-slot picker*); seluruh pesanan adalah *pick-up* "secepatnya" dengan informasi estimasi teks.
5. **Payment Gateway Asli, Upload Bukti Bayar & Fitur Refund**: Tidak ada integrasi Midtrans/Xendit, tidak ada unggah foto struk transfer, dan tidak ada mekanisme *refund* (pesanan yang sudah `paid` tidak dapat dibatalkan).
6. **Peran Kasir (`cashier`) & Halaman `/tentang` Terpisah**: Ditunda sebagai fase pengembangan opsional setelah fitur inti `customer` dan `admin` selesai.

---

## User Personas

1. **Pelanggan (Customer - Pengguna Terdaftar & Tamu)**
   - **Kebutuhan**: Melihat menu kopi yang tersedia beserta harganya melalui ponsel, tablet, atau laptop, memesan minuman tanpa antre lama di kasir, memantau apakah pesanan sudah siap diambil (`ready`), dan melihat kode pesanan (`GM-0042`) untuk ditunjukkan di kasir.
2. **Admin Kedai (Admin)**
   - **Kebutuhan**: Mengatur daftar menu dan mematikan menu yang sedang habis (`is_available = false`) secara cepat, mencari pesanan pelanggan berdasarkan `order_code`, memperbarui status pembuatan minuman (`pending -> preparing -> ready -> completed`), menandai pembayaran tunai (`cash`), serta membatalkan pesanan `unpaid` yang batal atau tidak diambil pelanggan.
3. **Peninjau Portofolio / Recruiter**
   - **Kebutuhan**: Mencoba alur pelanggan dan admin secara instan menggunakan akun demo dari *seeder*, melihat antarmuka yang rapi di berbagai ukuran layar, dan memverifikasi kualitas kode serta pengujian otomatis (*Feature Test*).

---

## Functional Requirements

### Brand Profile & Katalog Publik
- **FR-1**: Sistem harus menampilkan halaman Beranda (`/`) yang memuat *Brand Profile* ringan Gumi Coffee (cerita singkat brand, jam buka kedai yang dibaca langsung dari `config('gumi.operating_hours')`, serta lokasi kedai) dan tombol aksi nyata (**"Lihat Menu"**) menuju katalog menu. *(Diperjelas berdasarkan P5).*
- **FR-2**: Sistem harus menampilkan halaman Katalog Menu yang dapat diakses publik (tanpa login), dikelompokkan berdasarkan kategori sederhana (seperti `Coffee`, `Non-Coffee`, `Pastry`), menampilkan nama, deskripsi, harga dalam Rupiah, gambar menu (atau *fallback placeholder*), serta indikator jelas jika menu sedang tidak tersedia (`is_available = false`).

### Autentikasi, Peran & User Profile
- **FR-3**: Sistem harus menyediakan autentikasi berbasis Laravel Breeze (registrasi publik otomatis mendapat `role = customer`, login, dan logout) serta membedakan pengguna menjadi dua peran aktif melalui kolom `users.role`: `customer` dan `admin` (akun `admin` dibuat melalui *seeder*, bukan registrasi publik).
- **FR-4**: Sistem harus menyediakan halaman User Profile bagi pelanggan yang sudah login untuk mengubah nama, email, dan password, namun **menonaktifkan/menghapus fitur Hapus Akun (Delete Account)** bawaan Breeze demi menjaga integritas relasi `orders.user_id`.

### Keranjang Belanja (Session Cart) & Checkout Takeaway
- **FR-5**: Sistem harus mengizinkan pengunjung (baik tamu/belum login maupun `customer` yang sudah login) menambahkan menu yang berstatus `is_available = true` ke dalam keranjang belanja yang disimpan di **Laravel Session**, mengubah kuantitas item dengan batas bilangan bulat positif antara `1` hingga `99`, atau menghapus item dari keranjang. *(Diperjelas berdasarkan P3).*
- **FR-6**: Saat pengguna yang belum login menekan tombol menuju halaman **Checkout**, sistem harus mengarahkan pengguna ke halaman Login, mempertahankan isi keranjang belanja di dalam session setelah login berhasil, dan mengarahkan pengguna kembali (*intended redirect*) ke halaman Checkout.
- **FR-7**: Halaman Checkout harus menampilkan ringkasan item pesanan, informasi bahwa pemesanan hanya untuk **Takeaway / Pick-up di kasir** dengan teks estimasi waktu siap statis (misal: *"Estimasi siap 10-15 menit"*, tanpa penjadwalan jam ambil), satu kolom input catatan pesanan (`notes`, opsional, divalidasi maksimal `500` karakter), dan pilihan metode pembayaran (`cash` atau `qris_dummy`). *(Diperjelas berdasarkan P3).*
- **FR-8**: Sistem harus memvalidasi waktu pemesanan terhadap jam operasional harian kedai yang disimpan di satu konfigurasi terpusat (`config('gumi.operating_hours')`, format `HH:MM` pada hari yang sama) menggunakan zona waktu `Asia/Jakarta`; pesanan di luar jam buka harus ditolak dengan pesan kesalahan yang jelas. *(Diperjelas berdasarkan P5).*
- **FR-9**: Saat *checkout* diproses, sistem harus menjalankan satu transaksi database (`DB::transaction()`) yang: (a) mengecek ulang bahwa seluruh menu di keranjang masih ada di database dan berstatus `is_available = true` (jika ada menu yang tidak tersedia atau terhapus, batalkan *checkout* dan tampilkan pesan kesalahan yang menyebutkan nama menu tersebut), (b) menghitung harga satuan (`unit_price`), `subtotal`, dan `total` murni di sisi server, (c) menyimpan data pesanan ke tabel `orders` dengan `order_code = null` saat *insert* awal lalu segera memperbarui `order_code` (kolom `string(20)->nullable()->unique()`) dari `id` pesanan berformat `'GM-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)` di dalam transaksi yang sama, (d) menyalin `menu_name`, `unit_price`, `quantity`, dan `subtotal` ke tabel `order_items`, serta (e) mengosongkan keranjang di session. *(Diperjelas berdasarkan P2, P3).*

### Simulasi Pembayaran & Pembatalan Pelanggan
- **FR-10**: Untuk pesanan dengan `payment_method = qris_dummy`, halaman detail pesanan pelanggan harus menampilkan gambar QRIS simulasi berlabel tegas **"SIMULASI"** (bukan QRIS asli) beserta tombol **"Saya sudah bayar"**; aksi ini hanya diizinkan jika pemanggil adalah pemilik pesanan (`OrderPolicy`), `status = pending`, dan `payment_status = unpaid`, yang kemudian mengubah `payment_status` menjadi `paid` dan mengisi `paid_at` dengan waktu saat ini.
- **FR-11**: Pelanggan hanya dapat membatalkan (`status = cancelled`) pesanan miliknya sendiri apabila pesanan tersebut masih berstatus `status = pending` **DAN** `payment_status = unpaid`; pesanan yang sudah `paid` atau sudah melampaui status `pending` tidak dapat dibatalkan oleh pelanggan.
- **FR-12**: Sistem harus menyediakan halaman Riwayat Pesanan dan Detail Pesanan bagi pelanggan yang dibatasi oleh `OrderPolicy` sehingga pelanggan hanya dapat melihat, membayar simulasi QRIS, atau membatalkan pesanan miliknya sendiri (`orders.user_id === auth()->id()`).

### Manajemen Admin (Pesanan & Menu)
- **FR-13**: Seluruh rute manajemen admin (`/admin/*`) harus dilindungi oleh middleware khusus (`EnsureUserIsAdmin`) yang menolak akses dari pengguna non-admin (termasuk `customer`) dengan respons `403 Forbidden`.
- **FR-14**: Admin harus dapat melihat daftar pesanan masuk, memfilter berdasarkan status pesanan, mencari pesanan berdasarkan `order_code` secara *case-insensitive* dan mendukung pencocokan parsial (misal mengetik `0042` atau `gm-0042` untuk menemukan `GM-0042`), serta menandai lunas (`payment_status = paid` dan mengisi `paid_at`) untuk pesanan `payment_method = cash` yang masih berstatus `unpaid` selama `status` pesanan berada pada `pending`, `preparing`, atau `ready` (ditolak untuk pesanan berstatus `cancelled` atau `completed`). *(Diperjelas berdasarkan P2).*
- **FR-15**: Admin harus dapat memperbarui status pesanan melalui satu mekanisme transisi terpusat (Method/Enum) yang menolak loncatan status dan menerapkan aturan berikut:
  - Alur maju hanya berurutan: `pending -> preparing -> ready -> completed`.
  - Transisi `pending -> preparing` untuk `cash` diizinkan baik saat `unpaid` maupun `paid`, sedangkan untuk `qris_dummy` **wajib** `payment_status = paid`.
  - Transisi `ready -> completed` **wajib** memiliki `payment_status = paid` (baik `cash` maupun `qris_dummy`).
  - Pembatalan (`-> cancelled`) oleh Admin **hanya diizinkan jika `payment_status = unpaid`** dan status pesanan saat ini berada pada `pending`, `preparing`, **atau `ready`** (di mana `ready + unpaid` menangani kasus pesanan `cash` yang tidak diambil pelanggan). Pesanan yang sudah `paid` tidak dapat dibatalkan oleh siapa pun.
- **FR-16**: Admin harus dapat melakukan pengelolaan katalog menu (Create, Read, Update, Delete data pada tabel `menus`) dengan validasi `price` sebagai bilangan bulat positif (minimal `1000` Rupiah), pembuatan `slug` unik otomatis dari `name`, serta dapat mengubah status ketersediaan menu (`is_available`) secara cepat melalui tombol *quick-toggle*. *(Diperjelas berdasarkan P4).*
- **FR-17**: Sistem harus menyediakan `DatabaseSeeder` yang membuat 1 akun Admin demo, 1 akun Customer demo, serta sejumlah data menu contoh lintas kategori dengan aset gambar bawaan agar aplikasi langsung siap diuji.

---

## Non-Functional Requirements

- **NFR-1 (Pencegahan Race Condition dengan Row Locking)**: Seluruh perubahan status pesanan, aksi pembayaran (`qris_dummy` oleh pelanggan maupun tandai lunas `cash` oleh admin), dan aksi pembatalan pesanan **wajib** dijalankan di dalam `DB::transaction()` menggunakan `lockForUpdate()` pada baris `orders`, serta memvalidasi ulang syarat `status` dan `payment_status` setelah *lock* diperoleh (di mana `lockForUpdate()` memberikan *row-level lock* pada MySQL/PostgreSQL produksi, sementara di SQLite lokal pengujian memverifikasi konsistensi pengecekan ulang kondisi di dalam transaksi). *(Diperjelas berdasarkan P1).*
- **NFR-2 (Akurasi Zona Waktu Operasional)**: Aplikasi harus menetapkan konfigurasi `app.timezone` ke **`Asia/Jakarta`** secara eksplisit agar validasi jam buka kedai dan pencatatan waktu transaksi (`created_at`, `updated_at`, `paid_at`) konsisten dengan Waktu Indonesia Barat (WIB).
- **NFR-3 (Keamanan & Integritas Relasional)**: Kalkulasi total harga tidak boleh memercayai nilai harga dari *request* klien; `orders.user_id` harus menggunakan `restrictOnDelete()`; dan `order_items.menu_id` harus bersifat `nullable` dengan `nullOnDelete()` disertai salinan `menu_name` agar penghapusan atau perubahan menu oleh admin tidak merusak riwayat pesanan lama.
- **NFR-4 (Responsivitas & Identitas Visual Urban)**: Antarmuka harus responsif tanpa *horizontal overflow* di layar Mobile, Tablet, dan Desktop, menerapkan gaya **Urban / Neo-Brutalist** dengan palet warna `#b22e3b` (utama), `#fffaee` (utama terang), `#e9bfd2` (aksen), dan `#020202` (teks, *border* tegas, dan *hard offset shadow*), serta memenuhi standar kontras WCAG AA (teks `#020202` di atas `#fffaee`/`#e9bfd2`, dan teks `#fffaee` di atas `#b22e3b`).
- **NFR-5 (Kompatibilitas Deployment Serverless Vercel)**: Aplikasi harus kompatibel dengan lingkungan *read-only & ephemeral filesystem* di Vercel: menggunakan SQLite hanya untuk lokal/testing dan mendukung koneksi database relasional jarak jauh (MySQL/PostgreSQL) lewat variabel lingkungan produksi, mendukung driver sesi non-file (`cookie` atau `database`), mengarahkan path cache dan *compiled Blade views* (`VIEW_COMPILED_PATH`, `APP_CONFIG_CACHE`, `APP_ROUTES_CACHE`) ke `/tmp` dengan `LOG_CHANNEL=stderr` melalui konfigurasi `vercel.json` dan `api/index.php`, serta menyajikan gambar menu *seeder* dari aset statis `public/`. *(Diperjelas berdasarkan P6).*
- **NFR-6 (Struktur Ekstensibel Tanpa Over-Engineering)**: Skema `orders` tidak menyertakan kolom `order_type` dan `table_id` pada rilis awal, namun dirancang modular agar fitur *dine-in* / QR meja dan peran `cashier` dapat ditambahkan di masa depan melalui *migration* tanpa merombak tabel atau logika transaksi yang sudah ada.
- **NFR-7 (Cakupan Automated Feature Testing)**: Proyek harus menyertakan *Feature Test* otomatis (PHPUnit/Pest) yang memverifikasi: (1) alur *cart* tamu yang bertahan setelah login dan kalkulasi harga *checkout* di server beserta validasi jam buka & `is_available`, (2) penolakan akses `customer` ke rute `/admin/*` dan ketiadaan fitur hapus akun, (3) isolasi pesanan antar-pelanggan (`OrderPolicy`), serta (4) seluruh aturan validasi transisi status, pembayaran, dan pembatalan pesanan (termasuk penolakan mutasi berurutan yang melanggar syarat setelah mutasi pertama berhasil di dalam transaksi). *(Diperjelas berdasarkan P1).*

---

## Data Model & State Machine

### 1. Skema Tabel Inti

#### Tabel `users`
| Kolom | Tipe & Constraint | Keterangan |
|---|---|---|
| `id` | `bigint`, PK, auto-increment | Identitas pengguna |
| `name` | `string` | Nama lengkap pengguna |
| `email` | `string`, `unique` | Alamat email login |
| `password` | `string` | Password ter-hash |
| `role` | `string`, default `'customer'` | Nilai valid: `'customer'`, `'admin'` |
| `timestamps` | `created_at`, `updated_at` | Waktu pembuatan dan pembaruan |

#### Tabel `menus`
| Kolom | Tipe & Constraint | Keterangan |
|---|---|---|
| `id` | `bigint`, PK, auto-increment | Identitas menu |
| `name` | `string` | Nama menu kopi/makanan |
| `slug` | `string`, `unique` | Slug URL unik (di-generate unik dari `name`) |
| `category` | `string` | Kategori sederhana (misal: `Coffee`, `Non-Coffee`, `Pastry`) |
| `description` | `text`, `nullable` | Deskripsi singkat menu |
| `price` | `unsignedInteger` | Harga satuan dalam Rupiah (minimal `1000`) |
| `image_path` | `string`, `nullable` | Path aset statis/storage atau URL gambar (dengan fallback placeholder) |
| `is_available` | `boolean`, default `true` | Status ketersediaan menu |
| `timestamps` | `created_at`, `updated_at` | Waktu pembuatan dan pembaruan |

#### Tabel `orders`
| Kolom | Tipe & Constraint | Keterangan |
|---|---|---|
| `id` | `bigint`, PK, auto-increment | Identitas pesanan |
| `order_code` | `string(20)`, `nullable`, `unique` | Diisi `'GM-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)` setelah insert dalam transaksi yang sama |
| `user_id` | `foreignId`, FK ke `users.id`, `restrictOnDelete()` | Pemilik pesanan |
| `total` | `unsignedInteger` | Total harga pesanan dalam Rupiah (dihitung di server) |
| `status` | `string` | Nilai: `pending`, `preparing`, `ready`, `completed`, `cancelled` |
| `payment_method` | `string` | Nilai: `cash`, `qris_dummy` |
| `payment_status` | `string` | Nilai: `unpaid`, `paid` |
| `paid_at` | `timestamp`, `nullable` | Waktu pembayaran ditandai lunas |
| `notes` | `text`, `nullable` | Catatan keseluruhan pesanan dari pelanggan (maksimal `500` karakter) |
| `timestamps` | `created_at`, `updated_at` | Waktu pembuatan dan pembaruan |

#### Tabel `order_items`
| Kolom | Tipe & Constraint | Keterangan |
|---|---|---|
| `id` | `bigint`, PK, auto-increment | Identitas baris item pesanan |
| `order_id` | `foreignId`, FK ke `orders.id`, `cascadeOnDelete()` | Relasi ke pesanan induk |
| `menu_id` | `foreignId`, `nullable`, FK ke `menus.id`, `nullOnDelete()` | Referensi menu asli (tetap aman jika menu dihapus) |
| `menu_name` | `string` | Salinan (*snapshot*) nama menu saat *checkout* |
| `quantity` | `unsignedInteger` | Jumlah porsi yang dipesan (`1` s/d `99`) |
| `unit_price` | `unsignedInteger` | Salinan (*snapshot*) harga satuan saat *checkout* |
| `subtotal` | `unsignedInteger` | Hasil `quantity * unit_price` saat *checkout* |
| `timestamps` | `created_at`, `updated_at` | Waktu pembuatan dan pembaruan |

### 2. Matriks Aturan Transisi Status & Pembayaran (Dengan `lockForUpdate()`)

| Aksi | Pelaku | Syarat Status Pesanan (`status`) | Syarat Metode & Status Bayar | Hasil Akhir |
|---|---|---|---|---|
| **Bayar QRIS ("Saya sudah bayar")** | Pemilik Pesanan (`customer`) | Hanya `pending` | `payment_method = qris_dummy` DAN `payment_status = unpaid` | `payment_status = paid`, `paid_at = now()` |
| **Tandai Lunas (Cash)** | `admin` | `pending`, `preparing`, atau `ready` *(ditolak jika `cancelled` / `completed`)* | `payment_method = cash` DAN `payment_status = unpaid` | `payment_status = paid`, `paid_at = now()` |
| **Proses Pesanan (`pending -> preparing`)** | `admin` | Hanya dari `pending` | Jika `cash`: boleh `unpaid` atau `paid`. Jika `qris_dummy`: **wajib `paid`** | `status = preparing` |
| **Siap Diambil (`preparing -> ready`)** | `admin` | Hanya dari `preparing` | Berlaku untuk `cash` maupun `qris_dummy` | `status = ready` |
| **Selesai (`ready -> completed`)** | `admin` | Hanya dari `ready` | **Wajib `payment_status = paid`** | `status = completed` |
| **Batalkan Pesanan (Pelanggan)** | Pemilik Pesanan (`customer`) | Hanya dari `pending` | **Wajib `payment_status = unpaid`** | `status = cancelled` |
| **Batalkan Pesanan (Admin)** | `admin` | Dari `pending`, `preparing`, **atau `ready`** *(kasus `cash` tidak diambil)* | **Wajib `payment_status = unpaid`** | `status = cancelled` |

---

## User Journeys

### 1. Alur Pelanggan: Menjelajah Menu (Tamu) hingga Checkout & Simulasi QRIS
1. Pengunjung membuka halaman Beranda (`/`), membaca profil singkat Gumi Coffee dan jam buka, lalu menekan tombol **"Lihat Menu"**.
2. Pengunjung memilih beberapa menu yang berstatus *Available* dan menambahkannya ke keranjang (tersimpan di Session).
3. Pengunjung menekan tombol **"Checkout"**, diarahkan ke halaman **Login** (atau **Register**), lalu masuk dengan akun `customer`.
4. Sistem mempertahankan isi keranjang di session dan mengarahkan pelanggan langsung ke halaman **Checkout**.
5. Pelanggan memeriksa daftar pesanan dan teks *"Estimasi siap 10-15 menit"*, mengisi catatan `notes` (misal: *"Es dipisah jika memungkinkan"*), memilih metode pembayaran **`qris_dummy`**, dan menekan **"Buat Pesanan"**.
6. Sistem memvalidasi jam operasional (`Asia/Jakarta`) dan ketersediaan menu di dalam transaksi database, membuat pesanan dengan kode `GM-0001`, dan mengarahkan pelanggan ke halaman Detail Pesanan.
7. Pelanggan melihat gambar QRIS berlabel **"SIMULASI"** dan menekan tombol **"Saya sudah bayar"**. Status pembayaran berubah menjadi `paid` dan pesanan kini terkunci dari pembatalan serta siap diproses Admin.

### 2. Alur Pelanggan & Admin: Pesanan Tunai (`cash`) & Kasus Tidak Diambil
1. Pelanggan yang sudah login melakukan *checkout* dengan metode pembayaran **`cash`** (`status = pending`, `payment_status = unpaid`).
2. Selama status masih `pending` dan `unpaid`, pelanggan masih dapat membatalkan pesanan jika berubah pikiran.
3. Admin melihat pesanan `GM-0002` di dashboard Admin, lalu mengubah status dari `pending` ke `preparing`, dan setelah minuman selesai dibuat mengubahnya menjadi `ready`.
4. **Skenario Normal**: Pelanggan datang ke kasir menyebutkan `GM-0002` dan membayar tunai. Admin menekan **"Tandai Lunas"** (`payment_status = paid`), lalu mengubah status pesanan dari `ready` menjadi `completed`.
5. **Skenario Tidak Diambil**: Jika pelanggan tidak pernah datang mengambil pesanan `cash` yang sudah `ready` (dan masih `unpaid`), Admin tidak terjebak *deadlock*: Admin dapat menekan **"Batalkan Pesanan"** karena aturan mengizinkan Admin membatalkan dari status `ready` selama masih `unpaid`.

---

## Success Metrics

- **SM-1 (Kelengkapan & Kelulusan Test 100%)**: Seluruh *Feature Test* otomatis untuk *checkout*, otorisasi admin, isolasi pesanan pelanggan, dan aturan transisi status/pembayaran lulus 100% (`0 failures`).
- **SM-2 (Zero Deadlock & Zero Race Condition pada State Machine)**: Tidak ada jalur status pesanan yang dapat menggantung selamanya atau melanggar aturan *"pesanan `paid` tidak dapat dibatalkan"* melalui pengujian transisi dan penguncian `lockForUpdate()`.
- **SM-3 (Kesiapan Demo Instan)**: Evaluasi proyek oleh *recruiter* dapat langsung dilakukan setelah menjalankan migrasi dan `DatabaseSeeder` (atau langsung melalui URL *deployment* Vercel) dengan akun demo Admin dan Customer yang berfungsi penuh.
- **SM-4 (Responsivitas & Keterbacaan Visual)**: Seluruh halaman utama bebas dari *horizontal overflow* pada viewport Mobile (`375px`), Tablet (`768px`), dan Desktop (`1280px+`) dengan kontras teks memenuhi standar WCAG AA.

---

## Timeline

Karena proyek berskala **Small** (dikerjakan mandiri dalam target beberapa minggu), tahapan implementasi dibagi menjadi 3 tonggak ringkas:
- **Tahap 1 (Fondasi, Skema & Autentikasi)**: Setup Laravel + Breeze (tanpa hapus akun), konfigurasi timezone `Asia/Jakarta`, migrasi tabel (`users`, `menus`, `orders`, `order_items`), `DatabaseSeeder`, dan tema visual Urban.
- **Tahap 2 (Katalog, Session Cart, Checkout & State Machine Pesanan)**: Halaman Beranda (*Brand Profile*), Katalog Menu, Session Cart (termasuk alur tamu login ke checkout), transaksi Checkout + `order_code`, `OrderPolicy`, dan simulasi bayar/batal pelanggan dengan `lockForUpdate()`.
- **Tahap 3 (Panel Admin, Feature Tests & Konfigurasi Deploy Vercel)**: Middleware `EnsureUserIsAdmin`, CRUD Menu + *quick-toggle* `is_available`, manajemen status & pembayaran pesanan oleh Admin, penyusunan *Feature Test* lengkap, serta penyesuaian konfigurasi untuk Vercel.

---

## Decisions

1. **Skenario Pemesanan Hanya Takeaway / Pick-up Tanpa Varian dan Tanpa Stok Angka**
   - **Keputusan**: Hanya melayani *takeaway / pick-up* di kasir dengan estimasi waktu siap berupa teks statis, satu kolom `notes` per pesanan (maksimal `500` karakter), dan boolean `is_available` per menu.
   - **Alasan**: Menjaga fokus portofolio pada keandalan alur transaksi dan *state machine* untuk skala *Small* tanpa kerumitan matriks varian atau manajemen inventaris stok angka.
   - **Alternatif yang Ditolak**: *Dine-in* dengan nomor meja/QR, *delivery* kurir, e-commerce biji kopi, varian menu, dan penjadwalan jam ambil.

2. **Simulasi Pembayaran Tanpa Payment Gateway (`cash` & `qris_dummy`)**
   - **Keputusan**: Menggunakan dua metode simulasi (`cash` ditandai lunas oleh admin; `qris_dummy` menampilkan QRIS berlabel `"SIMULASI"` dengan tombol `"Saya sudah bayar"` oleh pelanggan) tanpa integrasi pihak ketiga atau unggah bukti transfer.
   - **Alasan**: Menghilangkan ketergantungan pada *sandbox API key* eksternal dan penyimpanan file bukti bayar (terutama di lingkungan serverless seperti Vercel) sambil tetap mendemonstrasikan logika perbedaan alur pesanan tunai vs non-tunai.
   - **Alternatif yang Ditolak**: Integrasi Midtrans/Xendit dan fitur unggah gambar bukti transfer manual.

3. **Aturan Pembatalan Pesanan (Opsi A) & Penanganan Deadlock Pesanan Cash Tidak Diambil**
   - **Keputusan**: Pesanan yang sudah `paid` **tidak bisa dibatalkan oleh siapa pun**. Pelanggan hanya boleh membatalkan saat `pending` dan `unpaid`. Admin boleh membatalkan saat `pending`, `preparing`, **atau `ready`** selama masih `unpaid`.
   - **Alasan**: Mencegah inkonsistensi data pembayaran karena sistem tidak memiliki fitur *refund*, sekaligus mencegah *deadlock* pada pesanan `cash` yang sudah diproses hingga `ready` namun pelanggannya tidak datang membayar/mengambil.
   - **Alternatif yang Ditolak**: Mengizinkan admin membatalkan pesanan yang sudah `paid` (Opsi B) atau membatasi pembatalan admin hanya pada `pending` dan `preparing` (yang menyebabkan pesanan `cash` berstatus `ready + unpaid` menggantung selamanya).

4. **Pembuatan `order_code` dari ID Pesanan dengan Kolom `string(20)->nullable()->unique()`**
   - **Keputusan**: Kolom `orders.order_code` dibuat `string(20)->nullable()->unique()`. Di dalam transaksi *checkout*, baris pesanan di-*insert* terlebih dahulu, lalu `order_code` langsung diisi dari `id` (`GM-0001`) sebelum transaksi di-*commit*. Pencarian `order_code` oleh Admin dibuat *case-insensitive* dan mendukung pencocokan parsial (`P2`).
   - **Alasan**: Menghindari kegagalan *insert* SQL akibat kolom `NOT NULL` sebelum `id` auto-increment terbentuk, mengakomodasi ekspansi digit saat `id >= 10000`, serta memudahkan pencarian cepat di kasir.
   - **Alternatif yang Ditolak**: Kolom `order_code` non-nullable saat *insert* awal atau pembuatan kode acak (*random string*) yang berisiko *collision*.

5. **Pencegahan Race Condition dengan `DB::transaction()` dan `lockForUpdate()`**
   - **Keputusan**: Seluruh aksi mutasi status pesanan, pembayaran, dan pembatalan wajib mengunci baris `orders` dengan `lockForUpdate()` di dalam transaksi database dan memvalidasi ulang syarat kondisi setelah *lock* didapat (`P1`).
   - **Alasan**: Mencegah kondisi balapan (*race condition*) pada database produksi (MySQL/PostgreSQL), misalnya ketika pelanggan menekan tombol *"Batalkan"* dan *"Saya sudah bayar"* secara bersamaan, sambil tetap dapat diuji logika pengecekan ulangnya di SQLite.
   - **Alternatif yang Ditolak**: Validasi status di level controller tanpa penguncian baris database.

6. **Snapshot `menu_name`, `unit_price`, dan Penonaktifan Hapus Akun Breeze**
   - **Keputusan**: Menyimpan salinan `menu_name` dan `unit_price` di `order_items` (dengan `menu_id` `nullable` + `nullOnDelete()`), serta menonaktifkan fitur hapus akun pada profil Laravel Breeze (dengan `orders.user_id` `restrictOnDelete()`).
   - **Alasan**: Menjaga keutuhan riwayat pesanan walaupun menu diubah/dihapus oleh admin, dan mencegah error *foreign key* atau hilangnya data transaksi akibat penghapusan akun pengguna.
   - **Alternatif yang Ditolak**: Menghapus pesanan secara *cascade* saat akun dihapus atau hanya mengandalkan relasi langsung ke tabel `menus` tanpa salinan nama menu.

7. **Arah Desain Urban / Neo-Brutalist**
   - **Keputusan**: Menggunakan palet warna `#b22e3b` (Merah Crimson), `#fffaee` (Krem Terang), `#e9bfd2` (Pink Muda), dan `#020202` (Hitam Pekat untuk teks, *border* tegas, dan *hard offset shadow*).
   - **Alasan**: Memberikan identitas visual brand kopi modern yang berkarakter kuat, kontras tinggi, dan berbeda dari tampilan template standar.
   - **Alternatif yang Ditolak**: Tampilan bawaan Tailwind/Breeze tanpa kustomisasi identitas brand.

8. **Target Deployment Vercel dengan Penanganan Read-Only Filesystem**
   - **Keputusan**: Menargetkan deployment ke Vercel dengan konfigurasi yang tidak bergantung pada penyimpanan file lokal permanen: SQLite hanya untuk lokal/testing, sesi menggunakan `cookie` atau `database`, *compiled views* & *cache* diarahkan ke `/tmp` dengan `LOG_CHANNEL=stderr` (`P6`), serta gambar katalog demo disajikan dari aset statis `public/`.
   - **Alasan**: Memudahkan peninjau portofolio mengakses demo langsung melalui URL publik Vercel tanpa mengalami error *read-only filesystem*.
   - **Alternatif yang Ditolak**: Mengandalkan file SQLite lokal dan path cache default `storage/framework/views` di dalam *serverless function* Vercel.

---

## Open Questions

- **OQ-1 (Penyedia Database Relasional Terkelola untuk Vercel) (assumption)**: Karena *filesystem* Vercel bersifat *ephemeral* (tidak dapat menyimpan file SQLite secara permanen di produksi), diasumsikan deployment produksi di Vercel akan dihubungkan ke layanan database relasional gratis/terkelola seperti **Neon (PostgreSQL)** atau **TiDB Cloud / Aiven (MySQL)** melalui variabel lingkungan `DB_*`, serta menggunakan `SESSION_DRIVER=cookie` atau `database`.
- **OQ-2 (Penyimpanan Upload Gambar Menu Baru di Produksi Vercel) (assumption)**: Untuk menu bawaan *seeder*, gambar disimpan sebagai aset statis di dalam repositori (`public/images/menus/`) sehingga selalu tampil di Vercel. Untuk fitur tambah/ubah gambar menu oleh Admin di lingkungan produksi Vercel, diasumsikan Admin dapat mengisi URL gambar atau menggunakan disk penyimpanan objek eksternal (seperti S3/Supabase/Cloudinary) jika ingin gambar hasil *upload* baru bertahan permanen, sementara di lingkungan lokal tetap dapat menggunakan `Storage::disk('public')`.
