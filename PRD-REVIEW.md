# PRD Review & Gap Analysis: Gumi Coffee

## 1. Overall Assessment

Dokumen `PRD.md` untuk **Gumi Coffee** (tipe `web app`, skala `Small`) sudah memiliki fondasi produk, batasan ruang lingkup (*scope*), skema data, dan aturan *state machine* pesanan yang sangat solid serta bebas dari *deadlock* bisnis. Namun, setelah dilakukan verifikasi terhadap perilaku nyata teknologi yang digunakan (**Laravel 11/12 + Breeze, SQLite vs MySQL/PostgreSQL, dan runtime serverless Vercel**), ditemukan **6 celah teknis dan validasi (*Medium* dan *Low impact*, `P1` s/d `P6`)** yang perlu diperjelas agar implementasi dan pengujian otomatis berjalan mulus tanpa kendala *runtime*. Tidak ditemukan kontradiksi bisnis berstatus *High impact* karena seluruh aturan pembayaran dan pembatalan (Opsi A serta penanganan pesanan `cash` tidak diambil) telah dikunci bersama pemilik produk.

---

## 2. Step 0: Grounding in Technical Reality

Klaim teknis di dalam `PRD.md` telah diverifikasi terhadap perilaku aktual *framework* dan infrastruktur target:

1. **Laravel Breeze Session Regeneration (`FR-6`)**: *Terverifikasi.* Metode `$request->session()->regenerate()` pada `AuthenticatedSessionController::store` di Laravel Breeze hanya memperbarui ID sesi (untuk mencegah *session fixation*) tanpa menghapus data *payload* sesi, sehingga data keranjang (`session('cart')`) milik tamu tetap utuh setelah login.
2. **Perilaku `lockForUpdate()` di SQLite vs MySQL/PostgreSQL (`NFR-1`, `NFR-5`, `NFR-7`)**: *Temuan (`P1`).* Grammar SQLite di Laravel mengabaikan klausa `FOR UPDATE` (mengembalikan string kosong) karena SQLite mengunci pada level database/file, bukan *row-level lock* seperti MySQL (InnoDB) atau PostgreSQL. Kode tetap wajib memanggil `lockForUpdate()` untuk produksi, tetapi pengujian di SQLite memverifikasi pengecekan ulang kondisi (*re-check preconditions*) di dalam transaksi.
3. **Perilaku `str_pad` untuk `order_code` (`FR-9`, `FR-14`)**: *Temuan (`P2`).* Fungsi `str_pad((string) $id, 4, '0', STR_PAD_LEFT)` menghasilkan 4 digit angka untuk ID `1..9999` (`GM-0001`), dan otomatis melebar menjadi 5+ digit (`GM-10000`) saat ID mencapai `10000` tanpa memotong angka. Kolom database harus memiliki kapasitas karakter yang cukup (minimal `VARCHAR(20)`) dan pencarian admin harus *case-insensitive*.
4. **Read-Only Filesystem pada Runtime Vercel (`NFR-5`)**: *Temuan (`P6`).* Selain tidak bisa menyimpan database SQLite dan file upload lokal secara permanen, *serverless function* Vercel menjalankan kode di sistem file *read-only* kecuali folder `/tmp`. Tanpa mengarahkan `VIEW_COMPILED_PATH`, `APP_CONFIG_CACHE`, `APP_ROUTES_CACHE`, dan `LOG_CHANNEL=stderr`, aplikasi Laravel akan gagal merender Blade di Vercel.

---

## 3. Summary of Findings (`P1` - `P6`)

| ID | Impact | Area | Ringkasan Temuan | Requirement Terdampak |
|---|---|---|---|---|
| **P1** | **Medium** | Database & Testing | Klausa `lockForUpdate()` merupakan *no-op* pada grammar SQLite saat testing lokal, meskipun aktif sebagai *row-level lock* di MySQL/PostgreSQL produksi. Spesifikasi test perlu menegaskan apa yang diuji di SQLite. | `NFR-1`, `NFR-7` |
| **P2** | **Medium** | Data Model & Search | Panjang kolom `orders.order_code` belum dispesifikasikan untuk mengantisipasi `id >= 10000` (`GM-10000`), dan perilaku pencarian `order_code` oleh Admin belum menetapkan aturan *case-insensitive* / *partial match*. | `FR-9`, `FR-14` |
| **P3** | **Medium** | Validation & UX | Batas input kuantitas item keranjang (`quantity`), batas panjang karakter catatan pesanan (`notes`), serta perilaku umpan balik (*feedback*) spesifik saat item di keranjang mendadak berubah menjadi `is_available = false` ketika *checkout* belum dirinci. | `FR-5`, `FR-7`, `FR-9` |
| **P4** | **Medium** | Admin Menu CRUD | Aturan pembuatan/validasi `slug` unik dan batas nilai `price` (bilangan bulat positif Rupiah) saat Admin menambah atau mengubah menu di `menus` belum disebutkan secara eksplisit. | `FR-16` |
| **P5** | **Low** | Config Consistency | Sumber data jam operasional yang ditampilkan di Beranda (`FR-1`) dan yang divalidasi saat Checkout (`FR-8`) perlu ditegaskan berasal dari satu kunci konfigurasi yang sama (`config('gumi.operating_hours')`) dalam format `HH:MM` WIB. | `FR-1`, `FR-8` |
| **P6** | **Low** | Vercel Deployment | Konfigurasi `NFR-5` belum mencantumkan kebutuhan pengalihan direktori *compiled Blade views* dan *cache* Laravel ke `/tmp` serta `LOG_CHANNEL=stderr` agar tidak terkena error *read-only filesystem* di Vercel. | `NFR-5` |

---

## 4. Specific Recommendations & Rationale for Changes

### A. Structure & Clarity
1. **Konsistensi Konfigurasi Jam Buka (`P5`)**:
   - *Rekomendasi*: Pastikan `FR-1` (tampilan jam buka di Beranda) dan `FR-8` (validasi jam buka saat *checkout*) membaca nilai dari satu konfigurasi terpusat `config('gumi.operating_hours')` (format jam harian `HH:MM`, misal `08:00` sampai `22:00` dengan timezone `Asia/Jakarta`).
   - *Alasan Perubahan*: Menghindari ketidaksinkronan antara jam buka yang dibaca pelanggan di halaman depan dengan jam buka yang divalidasi oleh sistem *checkout*.

2. **Spesifikasi Pencarian & Kolom `order_code` (`P2`)**:
   - *Rekomendasi*: Tetapkan `orders.order_code` menggunakan `string(20)->nullable()->unique()` dan pertegas di `FR-14` bahwa pencarian `order_code` mendukung pencarian *case-insensitive* maupun potongan kode (misal mengetik `0042` atau `gm-0042` tetap menemukan `GM-0042`).
   - *Alasan Perubahan*: Memudahkan operasional admin di kasir saat memanggil/mencari pesanan dengan cepat dan mencegah kegagalan panjang kolom jika jumlah transaksi melampaui 9.999 pesanan.

### B. Completeness & Feasibility
3. **Batas Validasi Input Keranjang, Catatan & Penanganan Menu Habis Saat Checkout (`P3`)**:
   - *Rekomendasi*:
     - Di `FR-5`: Kuantitas per item di keranjang wajib bilangan bulat antara `1` hingga `99`.
     - Di `FR-7`: Kolom `notes` dibatasi maksimal `500` karakter (`nullable|string|max:500`).
     - Di `FR-9`: Jika saat *checkout* ditemukan menu di keranjang yang sudah tidak tersedia (`is_available = false`) atau terhapus dari database, transaksi *checkout* ditolak dan pelanggan dikembalikan ke halaman keranjang/checkout dengan pesan kesalahan spesifik yang menyebutkan nama menu terkait.
   - *Alasan Perubahan*: Mencegah *integer overflow* pada kalkulasi `subtotal`/`total`, mencegah *payload* teks berlebih pada kolom `notes`, dan memberikan kejelasan UX kepada pelanggan mengenai item mana yang harus dihapus dari keranjang.

4. **Aturan Validasi CRUD Menu Admin (`P4`)**:
   - *Rekomendasi*: Di `FR-16`, nyatakan bahwa pembuatan/pembaruan menu wajib memvalidasi `price` sebagai bilangan bulat positif (`integer|min:1000`), `category` wajib diisi dari daftar kategori yang valid, dan `slug` dibuat otomatis secara unik dari `name` (misal dengan penambahan sufiks jika nama menu sama).
   - *Alasan Perubahan*: Mencegah *unhandled database exception* (`UniqueConstraintViolationException`) pada kolom `menus.slug` ketika Admin menginput nama menu serupa.

5. **Realita Pengujian `lockForUpdate()` dan Runtime Serverless Vercel (`P1`, `P6`)**:
   - *Rekomendasi*:
     - Di `NFR-1` & `NFR-7`: Tegaskan bahwa `lockForUpdate()` wajib dipanggil di kode produksi pada baris `orders` di dalam `DB::transaction()`, sedangkan *Feature Test* memastikan bahwa setiap percobaan mutasi kedua yang tidak lagi memenuhi syarat setelah mutasi pertama selesai akan selalu ditolak oleh pengecekan kondisi di dalam transaksi.
     - Di `NFR-5`: Tambahkan persyaratan konfigurasi runtime Vercel (`api/index.php`, `vercel.json`, pengalihan `VIEW_COMPILED_PATH` dan path cache ke `/tmp`, serta `LOG_CHANNEL=stderr`).
   - *Alasan Perubahan*: Menjamin ekspektasi yang akurat antara pengujian berbasis SQLite dan produksi berbasis MySQL/PostgreSQL, serta mencegah kegagalan *deployment* pertama ke Vercel akibat *read-only filesystem*.

### C. Prioritization & Implementation Sequence (MoSCoW Readiness)
Untuk menjaga skala **Small** tetap ramping saat diekstrak ke `FEATURES.md` dan `RFCs/`:
- **Must Have**: Fondasi & Auth Breeze tanpa hapus akun (`FR-3`, `FR-4`), Skema Database & Seeder (`FR-17`, `NFR-2`, `NFR-3`), Katalog Publik & Session Cart Tamu (`FR-2`, `FR-5`, `FR-6`), Transaksi Checkout & `order_code` (`FR-7`, `FR-8`, `FR-9`), State Machine Pembayaran & Transisi Status Terpusat dengan `lockForUpdate()` (`FR-10` s/d `FR-15`, `NFR-1`), CRUD Menu Admin (`FR-16`), Identitas Visual Urban Responsif (`FR-1`, `NFR-4`), dan Feature Tests (`NFR-7`).
- **Should Have**: Konfigurasi *deployment* Vercel (`NFR-5`) dan struktur modular untuk ekspansi masa depan (`NFR-6`).
- **Won't Have (Initial Release)**: *Dine-in* / nomor meja, *delivery*, varian menu, stok angka, penjadwalan jam ambil, *payment gateway* asli, fitur *refund*, peran `cashier`, dan halaman `/tentang` terpisah.
