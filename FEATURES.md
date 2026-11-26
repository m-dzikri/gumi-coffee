# Features List: Gumi Coffee

## Product Overview

**Gumi Coffee** adalah aplikasi web profil brand ringan (*lightweight brand profile*) dan pemesanan kopi *takeaway / pick-up* berbasis **Laravel + Breeze (Blade, Tailwind CSS, Alpine.js)** dengan identitas visual **Urban / Neo-Brutalist** (`#b22e3b`, `#fffaee`, `#e9bfd2`, `#020202`) yang responsif di perangkat Mobile, Tablet, dan Desktop. Aplikasi ini memfasilitasi dua peran aktif (`customer` dan `admin`): pelanggan dapat menjelajahi katalog menu, menyusun keranjang belanja berbasis sesi (termasuk saat masih berstatus tamu sebelum login), melakukan *checkout takeaway* di dalam jam operasional (`Asia/Jakarta`), mendapatkan kode pesanan pendek (`GM-0001`), serta melakukan simulasi pembayaran (`cash` atau `qris_dummy`). Admin mengelola katalog menu beserta status ketersediaannya (`is_available`) dan memproses siklus status pesanan serta pembayaran melalui satu *state machine* terpusat yang dilindungi transaksi database dan `lockForUpdate()`.

---

## Feature Summary

### 1. Count by Priority (MoSCoW)

| Priority | Count | Feature IDs |
|---|---|---|
| **Must** | 12 | `F1`, `F2`, `F3`, `F4`, `F5`, `F6`, `F7`, `F8`, `F9`, `F10`, `F11`, `F12` |
| **Should** | 1 | `F13` |
| **Could** | 0 | - |
| **Won't** | 0 | *(Didaftarkan di bawah tabel sesuai panduan proyek skala Small)* |
| **Total** | **13** | `F1` s/d `F13` |

### 2. Count by Category

| Category | Must | Should | Could | Won't | Total |
|---|---|---|---|---|---|
| **1. Brand Profile & Katalog Menu Publik** | 2 | 0 | 0 | 0 | **2** |
| **2. Autentikasi, Otorisasi & Profil Pengguna** | 3 | 0 | 0 | 0 | **3** |
| **3. Keranjang Belanja, Checkout Takeaway & Pembayaran Pelanggan** | 4 | 0 | 0 | 0 | **4** |
| **4. Manajemen Operasional Admin & Infrastruktur Deployment** | 3 | 1 | 0 | 0 | **4** |
| **Total** | **12** | **1** | **0** | **0** | **13** |

---

## Feature Details by Category

### 1. Brand Profile & Katalog Menu Publik

| ID | Feature | Priority | Source | Complexity | Acceptance Criteria |
|---|---|---|---|---|---|
| **F1** | Halaman Beranda & Brand Profile Urban | Must | FR-1, NFR-4 | Low | 1. Halaman beranda (`/`) menampilkan cerita singkat Gumi Coffee, lokasi kedai, dan jam operasional harian yang dibaca langsung dari `config('gumi.operating_hours')` (`P5`).<br>2. Menyediakan tombol CTA nyata (**"Lihat Menu"**) yang mengarah ke halaman katalog menu.<br>3. Menerapkan identitas visual **Urban / Neo-Brutalist** dengan palet `#b22e3b`, `#fffaee`, `#e9bfd2`, dan `#020202` (*border* tegas dan *hard offset shadow*) dengan kontras teks memenuhi standar WCAG AA.<br>4. Tata letak responsif tanpa *horizontal overflow* pada viewport Mobile (`375px`), Tablet (`768px`), dan Desktop (`1280px+`). |
| **F2** | Katalog Menu Publik & Pengelompokan Kategori | Must | FR-2, NFR-4 | Low | 1. Halaman katalog menu dapat diakses oleh pengunjung publik tanpa harus login.<br>2. Menampilkan daftar menu yang dikelompokkan berdasarkan kategori sederhana (misal: `Coffee`, `Non-Coffee`, `Pastry`).<br>3. Setiap kartu menu menampilkan nama, deskripsi singkat, harga dalam format Rupiah, dan gambar menu (menggunakan *fallback placeholder* yang rapi jika `image_path` kosong).<br>4. Menu dengan `is_available = false` menampilkan indikator status "Habis / Tidak Tersedia" secara jelas dan tombol tambah ke keranjangnya dinonaktifkan. |

### 2. Autentikasi, Otorisasi & Profil Pengguna

| ID | Feature | Priority | Source | Complexity | Acceptance Criteria |
|---|---|---|---|---|---|
| **F3** | Autentikasi Laravel Breeze, Role Pengguna & Database Seeder | Must | FR-3, FR-17 | Low | 1. Menyediakan fitur registrasi pelanggan baru, login, dan logout berbasis Laravel Breeze.<br>2. Setiap pengguna yang mendaftar lewat form registrasi publik otomatis mendapatkan `role = 'customer'` pada tabel `users` (tidak ada registrasi publik untuk `admin`).<br>3. `DatabaseSeeder` membuat minimal 1 akun Admin demo, 1 akun Customer demo, serta sejumlah data menu contoh lintas kategori yang menggunakan aset gambar statis bawaan di `public/` sehingga aplikasi langsung siap diuji oleh *recruiter*. |
| **F4** | User Profile Pelanggan Tanpa Fitur Hapus Akun | Must | FR-4, NFR-3 | Low | 1. Pelanggan yang sudah login dapat mengubah nama, alamat email, dan password melalui halaman profil bawaan Breeze.<br>2. Rute `DELETE /profile`, aksi `ProfileController::destroy`, dan komponen UI **Delete Account** dinonaktifkan/dihapus sepenuhnya sehingga akun tidak dapat dihapus dan relasi `orders.user_id` (`restrictOnDelete()`) tetap terlindungi. |
| **F5** | Proteksi Rute Admin (`EnsureUserIsAdmin`) & Isolasi Pesanan (`OrderPolicy`) | Must | FR-12, FR-13 | Low | 1. Middleware `EnsureUserIsAdmin` melindungi seluruh rute di bawah `/admin/*` dan mengembalikan respons `403 Forbidden` jika diakses oleh pengguna ber-role `customer` (atau mengarahkan ke login jika belum terautentikasi).<br>2. `OrderPolicy` membatasi akses halaman detail pesanan, aksi simulasi bayar `qris_dummy`, dan aksi pembatalan pesanan hanya untuk pemilik pesanan (`orders.user_id === auth()->id()`); akses ke pesanan pelanggan lain ditolak dengan `403 Forbidden`. |

### 3. Keranjang Belanja, Checkout Takeaway & Pembayaran Pelanggan

| ID | Feature | Priority | Source | Complexity | Acceptance Criteria |
|---|---|---|---|---|---|
| **F6** | Keranjang Belanja Session (Tamu & Pelanggan) dengan Redirect Login ke Checkout | Must | FR-5, FR-6 | Medium | 1. Pengunjung tamu (belum login) maupun `customer` (sudah login) dapat menambahkan menu berstatus `is_available = true` ke dalam keranjang belanja yang disimpan di **Laravel Session**.<br>2. Pengguna dapat mengubah kuantitas item di keranjang dengan batas validasi bilangan bulat antara `1` hingga `99` (`P3`) atau menghapus item dari keranjang; menu dengan `is_available = false` ditolak saat ditambahkan ke keranjang.<br>3. Jika tamu menekan tombol menuju halaman **Checkout**, sistem mengarahkan ke halaman Login, mempertahankan seluruh isi keranjang di dalam session setelah login berhasil, dan mengarahkan kembali (*intended redirect*) langsung ke halaman Checkout. |
| **F7** | Checkout Takeaway, Validasi Jam Buka & Transaksi Pembuatan `order_code` | Must | FR-7, FR-8, FR-9, NFR-2, NFR-3 | Medium | 1. Halaman Checkout menampilkan ringkasan item keranjang, keterangan pemesanan khusus **Takeaway / Pick-up di kasir**, teks estimasi waktu siap statis (misal *"Estimasi siap 10-15 menit"*, tanpa penjadwalan jam ambil), input `notes` (opsional, maksimal `500` karakter, `P3`), dan pilihan `payment_method` (`cash` atau `qris_dummy`).<br>2. Menolak *checkout* dengan pesan jelas apabila waktu server saat ini (`Asia/Jakarta`) berada di luar rentang jam operasional harian `config('gumi.operating_hours')` (`P5`) atau jika keranjang kosong.<br>3. Menjalankan proses *checkout* di dalam satu `DB::transaction()`: mengecek ulang seluruh item keranjang terhadap database (jika ada menu yang tidak ditemukan atau `is_available = false`, batalkan transaksi dan kembalikan pesan error yang menyebutkan nama menu tersebut, `P3`).<br>4. Menghitung `unit_price`, `subtotal`, dan `total` murni di sisi server (mengabaikan input harga dari klien).<br>5. Menyimpan baris baru ke `orders` (`order_code = null`, `status = 'pending'`, `payment_status = 'unpaid'`, `paid_at = null`), lalu segera memperbarui kolom `order_code` (`string(20)->nullable()->unique()`) dari `id` pesanan berformat `'GM-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)` (`P2`) di dalam transaksi yang sama sebelum *commit*.<br>6. Menyimpan baris-baris `order_items` dengan menyalin `menu_name`, `unit_price`, `quantity`, dan `subtotal` (`menu_id` `nullable` dengan `nullOnDelete()`), lalu mengosongkan keranjang di session. |
| **F8** | Simulasi Pembayaran QRIS Dummy & Pembatalan Pesanan oleh Pelanggan | Must | FR-10, FR-11, NFR-1 | Medium | 1. Untuk pesanan dengan `payment_method = qris_dummy`, halaman detail pesanan menampilkan gambar QRIS simulasi berlabel tegas **"SIMULASI"** dan tombol **"Saya sudah bayar"**.<br>2. Aksi **"Saya sudah bayar"** dijalankan di dalam `DB::transaction()` dengan `lockForUpdate()` pada baris `orders` dan pengecekan ulang syarat setelah *lock* (`P1`): hanya diizinkan bagi pemilik pesanan (`OrderPolicy`) ketika `payment_method = qris_dummy`, `status = pending`, dan `payment_status = unpaid`; aksi ini mengubah `payment_status = 'paid'` dan mengisi `paid_at = now()` (ditolak jika pesanan sudah `cancelled`, sudah lewat dari `pending`, atau sudah `paid`).<br>3. Aksi **Batalkan Pesanan** oleh pelanggan dijalankan di dalam `DB::transaction()` dengan `lockForUpdate()` dan pengecekan ulang syarat setelah *lock* (`P1`): hanya diizinkan bagi pemilik pesanan ketika `status = pending` **DAN** `payment_status = unpaid`, mengubah `status = 'cancelled'`; ditolak jika pesanan sudah `paid` atau status bukan `pending`. |
| **F9** | Riwayat & Detail Status Pesanan Pelanggan | Must | FR-12 | Low | 1. Menyediakan halaman daftar Riwayat Pesanan milik pelanggan yang sedang login, diurutkan dari pesanan terbaru.<br>2. Halaman Detail Pesanan menampilkan `order_code` (misal `GM-0042`), status pesanan terkini (`pending`, `preparing`, `ready`, `completed`, `cancelled`), metode dan status pembayaran (`unpaid`/`paid` beserta `paid_at`), catatan `notes`, teks estimasi waktu siap, serta rincian item dari snapshot `order_items` (`menu_name`, `quantity`, `unit_price`, `subtotal`, dan `total`). |

### 4. Manajemen Operasional Admin & Infrastruktur Deployment

| ID | Feature | Priority | Source | Complexity | Acceptance Criteria |
|---|---|---|---|---|---|
| **F10** | Dashboard Pesanan Admin, Pencarian `order_code` & Penandaan Lunas Tunai (`cash`) | Must | FR-14, NFR-1 | Medium | 1. Admin dapat melihat daftar seluruh pesanan pelanggan dengan filter berdasarkan `status` pesanan.<br>2. Admin dapat mencari pesanan berdasarkan `order_code` secara *case-insensitive* dan mendukung pencocokan parsial (misal mengetik `0042` atau `gm-0042` menemukan `GM-0042`, `P2`).<br>3. Admin dapat menandai lunas (`payment_status = 'paid'`, `paid_at = now()`) pesanan dengan `payment_method = cash` di dalam `DB::transaction()` menggunakan `lockForUpdate()` dan pengecekan ulang kondisi setelah *lock* (`P1`), hanya jika `payment_status = unpaid` dan `status` pesanan berada pada `pending`, `preparing`, atau `ready` (ditolak untuk pesanan berstatus `cancelled`, `completed`, atau yang sudah `paid`). |
| **F11** | State Machine Transisi Status & Pembatalan Pesanan Terpusat oleh Admin | Must | FR-15, NFR-1 | Medium | 1. Seluruh aturan transisi status pesanan dipusatkan di satu tempat (Method/Enum) dan dieksekusi di dalam `DB::transaction()` dengan `lockForUpdate()` serta pengecekan ulang syarat setelah *lock* (`P1`).<br>2. Hanya mengizinkan transisi maju secara berurutan oleh Admin: `pending -> preparing -> ready -> completed` (menolak seluruh loncatan status atau transisi mundur).<br>3. Transisi `pending -> preparing` untuk `payment_method = cash` diizinkan saat `unpaid` maupun `paid`, sedangkan untuk `payment_method = qris_dummy` **wajib** `payment_status = paid` (ditolak jika masih `unpaid`).<br>4. Transisi `ready -> completed` **wajib** memiliki `payment_status = paid` (ditolak jika masih `unpaid`).<br>5. Pembatalan pesanan (`-> cancelled`) oleh Admin hanya diizinkan jika `payment_status = unpaid` dan `status` saat ini berada pada `pending`, `preparing`, **atau `ready`** (di mana `ready + unpaid` menangani kasus pesanan `cash` yang tidak diambil pelanggan); pesanan yang sudah `paid` selalu ditolak untuk dibatalkan oleh siapa pun. |
| **F12** | Manajemen Katalog Menu (CRUD) & Quick-Toggle Ketersediaan oleh Admin | Must | FR-16, NFR-3 | Medium | 1. Admin dapat menambah, melihat daftar, mengubah, dan menghapus menu pada tabel `menus`.<br>2. Validasi input menu mewajibkan `name`, `category` yang valid, `price` sebagai bilangan bulat positif minimal `1000` Rupiah, serta pembuatan `slug` unik otomatis dari `name` (`P4`).<br>3. Menyediakan tombol *quick-toggle* pada halaman daftar menu admin untuk mengubah nilai boolean `is_available` secara langsung.<br>4. Penghapusan menu oleh Admin tetap menjaga keutuhan riwayat pesanan lama karena `order_items.menu_id` menggunakan `nullOnDelete()` dan `order_items.menu_name` menyimpan salinan nama menu. |
| **F13** | Konfigurasi Runtime Serverless Vercel | Should | NFR-5 | Low | 1. Menyediakan file konfigurasi `vercel.json` dan entrypoint `api/index.php` untuk menjalankan Laravel di lingkungan serverless Vercel (`P6`).<br>2. Mengarahkan path tulis runtime (`VIEW_COMPILED_PATH`, `APP_CONFIG_CACHE`, `APP_ROUTES_CACHE`) ke direktori `/tmp` serta mendukung `LOG_CHANNEL=stderr` dan `SESSION_DRIVER=cookie` (atau `database`) agar tidak gagal pada *read-only filesystem* Vercel.<br>3. Mendukung konfigurasi database relasional eksternal (PostgreSQL/MySQL) untuk produksi di Vercel sambil mempertahankan SQLite untuk development lokal dan automated testing. |

NFR-1, NFR-2, NFR-3, NFR-4, NFR-5, NFR-6, NFR-7: carried by RULES.md.

Out of scope / Won't Have for initial release (Small project): fitur *dine-in* dan nomor meja/scan QR meja (`order_type`, `table_id`), layanan *delivery* dan e-commerce biji kopi, varian menu dan stok angka, penjadwalan jam ambil, integrasi *payment gateway* asli (Midtrans/Xendit) dan upload bukti bayar, mekanisme *refund*, peran kasir (`cashier`), serta halaman `/tentang` terpisah.

---

## Open Questions

Tidak ada asumsi atau pertanyaan terbuka baru yang ditambahkan pada tahap ekstraksi fitur ini. Dua asumsi infrastruktur terkait penyedia database relasional terkelola dan penyimpanan gambar upload baru untuk produksi Vercel mengacu langsung pada `OQ-1` dan `OQ-2` di dalam `PRD.md`.
