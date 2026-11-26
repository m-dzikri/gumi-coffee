# Change Request: CR-001 — Perubahan Arah Desain Visual ke Gaya Playful Pop-Editorial ("Soletta Coffee Club") dengan Palet Warna Asli

- **Change ID**: `CR-001`
- **Date**: 2026-11-26
- **Requested By**: Product Owner / User
- **Decision**: Accepted (2026-11-26)

---

## Overall Assessment Summary

Perubahan yang diajukan mengganti arah estetika dan komposisi tata letak antarmuka **Gumi Coffee** dari gaya **Urban / Neo-Brutalist** (kotak ber-border hitam tebal dengan *hard offset shadow*) menjadi gaya **Playful Pop-Editorial / Retro Coffee Club** sesuai gambar referensi (*Soletta Coffee Club* oleh Made By End), **namun tetap mempertahankan 100% palet warna asli yang telah ditetapkan sejak awal** (`#b22e3b`, `#fffaee`, `#e9bfd2`, `#020202`).

Karena seluruh RFC (`RFC-001`, `RFC-002`, `RFC-003`) saat ini masih berstatus **`Not started`** (belum ada kode fitur atau Blade view kustom yang diimplementasikan), perubahan ini memiliki **risiko teknis sangat rendah (zero code rework)** dan tidak mengubah skema database, *state machine* pesanan, maupun logika bisnis transaksi. Perubahan hanya perlu diselaraskan pada dokumen spesifikasi (`PRD.md`, `FEATURES.md`, `RULES.md`, dan `RFCs/RFC-001-Foundation-Auth-Catalog.md`) sebelum `/implement-rfc RFC-001` dijalankan.

---

## 1. Change Classification

| Change ID | Title | Category | Size | Priority | Decision |
|---|---|---|---|---|---|
| **CR-001** | Adaptasi Tata Letak & Identitas Visual Gaya *Playful Pop-Editorial* (*Soletta Coffee Club*) dengan Palet Warna Asli (`#b22e3b`, `#fffaee`, `#e9bfd2`, `#020202`) | **Feature Modification** (`F1`, `F2`) & **Technical/Design Rule Change** (`UI-1`, `NFR-4`) | **Small** | **Must have** | **Decision: Accepted** |

### Rincian Adaptasi Visual dari Gambar Referensi ke Palet Gumi Coffee
1. **Palet Warna Tetap Dipertahankan (`#b22e3b`, `#fffaee`, `#e9bfd2`, `#020202`)**:
   - **`#b22e3b` (Merah Crimson Utama)**: Menggantikan peran warna biru kobalt/terakota pada gambar referensi untuk *navbar* solid, *headline* utama di *hero*, tombol CTA persegi tegas, pita *ticker/marquee* pembatas, lencana *starburst* (*sticker badge*), serta *footer*.
   - **`#fffaee` (Krem Terang Utama)**: Latar utama bagian *Hero*, bagian *Featured Menu Showcase* (*"MEET YOUR MORNING PEOPLE"*), pita *tagline* pra-footer, serta warna teks kontras tinggi di atas latar `#b22e3b` dan `#020202`.
   - **`#e9bfd2` (Pink Muda Aksen)**: Latar pita pengumuman paling atas (*top announcement bar*), bentuk lingkaran datar besar (*flat circle backdrop*) di belakang visual produk pada *Hero*, serta blok editorial kanan pada bagian *50/50 split story*.
   - **`#020202` (Hitam Pekat)**: Warna teks utama dan tipografi di atas latar `#fffaee` dan `#e9bfd2`, serta latar pita kontras gelap untuk seksi pemilih karakter/kategori menu (*"WHAT'S YOUR TYPE?"*) agar variasi blok warna antar-seksi tetap tegas dan memenuhi kontras WCAG AA.
2. **Struktur Seksi Beranda (`GET /`) Mengikuti Komposisi Referensi**:
   - **Top Announcement Strip (`#e9bfd2`)**: Teks *all-caps* tebal terpusat (misal: informasi singkat *takeaway* & jam buka dari `config('gumi.operating_hours')`).
   - **Solid Brand Navbar (`#b22e3b`)**: Logo tipografi tebal **gumi COFFEE CLUB** di kiri, tautan navigasi *all-caps* tegas di kanan (`LIHAT MENU`, `#cerita-kami`, keranjang, dan masuk/pesanan).
   - **Hero Section (`#fffaee`)**: Tata letak 2 kolom — di kiri: *headline* *all-caps* ekstra tebal bertumpuk dengan baris kedua miring (*italic display*, misal `"GOOD COFFEE. ZERO SNOOZE."` / `"KOPI TEGAS. TANPA ANTRE."`), subjudul ringkas, dan tombol CTA persegi solid `#b22e3b` (**"Lihat Menu"** menuju `/menus`); di kanan: visual produk unggulan di depan lingkaran datar `#e9bfd2` disertai stiker bintang (*starburst badge*) miring (misal `"ESTIMASI SIAP 10-15 MENIT"`).
   - **Ticker / Feature Divider Strip (`#b22e3b`)**: Pita horizontal berisi poin karakter Gumi Coffee huruf kapital yang dipisahkan ikon matahari/sinar (*sunburst icon*).
   - **Menu Highlight Showcase — *"MEET YOUR MORNING PEOPLE."* (`#fffaee`)**: Judul *all-caps* terpusat dengan 3 kartu menu unggulan tanpa *border* kotak kaku, menampilkan visual menu, nama *all-caps* tebal di bawahnya, karakter rasa yang dipisahkan tanda titik (`RICH • NUTTY • SMOOTH`), serta harga Rupiah.
   - **Interactive Category / Type Banner — *"WHAT'S YOUR TYPE?"* (`#020202` / `#b22e3b`)**: Judul besar bertumpuk di kiri dengan tombol filter persegi bergaris tepi tegas (`COFFEE`, `NON-COFFEE`, `PASTRY` yang mengarah langsung ke kategori terkait di `/menus` agar mematuhi aturan anti-tombol-mati `UI-2`), serta ilustrasi cangkir kopi dengan motif matahari terbit (*rising sun*) di kanan.
   - **50/50 Split Story & Operasional — *"LESS GUESS. MORE YES."* (`#e9bfd2`)**: Kolom kiri berupa visual seduh/kedai *full-bleed*; kolom kanan berlatar `#e9bfd2` memuat cerita singkat Gumi Coffee, alamat kedai (`config('gumi.location')`), jam operasional (`config('gumi.operating_hours')`), dan tombol CTA menuju `/menus`.
   - **Pre-Footer Tagline Banner (`#fffaee`) & Solid Footer (`#b22e3b`)**: Motif grafis matahari terbit (*sunburst*) di kiri, garis pemisah vertikal, dan *tagline* *bold-italic all-caps* (`"SERIOUS ABOUT COFFEE. NOT OURSELVES."`) di atas *footer* solid `#b22e3b`.
3. **Konsistensi di Seluruh Halaman Aplikasi (`/menus`, `/cart`, `/checkout`, `/orders`, `/admin/*`, Auth)**:
   - Mengadopsi bahasa visual yang sama: header pita warna solid (*color-blocked bands*), tipografi *display all-caps* tebal dengan aksen *italic*, motif *sunburst*, tombol persegi solid yang tajam, dan permukaan kartu yang bersih dengan kontras WCAG AA.

---

## 2. Impact Analysis

### A. Conflict Check (Pemeriksaan Konflik terhadap `RULES.md` & `PRD.md`)
1. **Konflik Sebagian dengan `UI-1` (`RULES.md`), `NFR-4` (`PRD.md`), dan `Decision 7` (`PRD.md`)**:
   - *Aturan/Keputusan Saat Ini*: Menetapkan gaya **"Urban / Neo-Brutalist"** dengan atribut spesifik *border* tegas (`2px solid #020202`) dan *hard offset shadow* (`4px 4px 0px #020202`) pada komponen kartu/tombol.
   - *Analisis Konflik*: Palet warna (`#b22e3b`, `#fffaee`, `#e9bfd2`, `#020202`), rasio kontras WCAG AA (`#020202` di atas `#fffaee`/`#e9bfd2`, serta `#fffaee` di atas `#b22e3b` dan `#020202`), dan syarat responsivitas (`375px`, `768px`, `1280px+`) **tidak berubah dan tetap patuh 100%**. Namun, istilah gaya **"Urban / Neo-Brutalist"** dengan kewajiban *hard offset shadow* digantikan oleh gaya **"Playful Pop-Editorial / Retro Coffee Club"** berbasis *full-width color-blocking*, tipografi *bold/italic all-caps*, motif *sunburst & starburst badge*, serta tombol persegi datar (*flat rectangular CTA*).
   - *Apakah Alasan Asli Decision 7 Masih Berlaku?*: Alasan asli Decision 7 adalah *"Memberikan identitas visual brand kopi modern yang berkarakter kuat, kontras tinggi, dan berbeda dari tampilan template standar."* Gaya baru dari gambar referensi tetap memenuhi alasan tersebut bahkan dengan struktur visual yang lebih hidup dan editorial.
2. **Kepatuhan terhadap `UI-2` (`RULES.md` — Larangan Tombol/Link Mati) & `AC-2` (`RFC-001`)**:
   - Pada gambar referensi terdapat tombol `"FIND YOUR FLAVOUR"`, `"BOLD / BRIGHT / SMOOTH"`, dan `"BREW BETTER"`.
   - Agar tidak melanggar `UI-2` (anti-*dead control*) dan tetap lulus pengujian otomatis `RFC-001 AC-2` (yang mencari tautan CTA **"Lihat Menu"** menuju `/menus`):
     - Tombol CTA utama menggunakan label **"Lihat Menu"** (atau menyertakan teks **"Lihat Menu"** yang mengarah ke `route('menus.index')` / `/menus`).
     - Tombol kategori pada seksi *"WHAT'S YOUR TYPE?"* (`Coffee`, `Non-Coffee`, `Pastry`) dihubungkan langsung ke anchor/filter kategori nyata di halaman `/menus` (misal `/menus#category-coffee`), bukan tombol dekoratif tanpa aksi.
3. **Kepatuhan terhadap Batasan Scope (`PRD.md` — Out of Scope #2: E-Commerce Biji Kopi)**:
   - Gambar referensi menampilkan kemasan kantong biji kopi (*whole bean 250g*). Sesuai `Decision 1` dan *Out of Scope #2* di `PRD.md`, domain produk Gumi Coffee tetap berupa **pemesanan minuman kopi, non-kopi, dan pastry untuk *takeaway / pick-up* di kasir**, sehingga visual produk pada kartu/hero disesuaikan untuk menu Gumi Coffee tanpa mengubah cakupan bisnis menjadi pengiriman biji kopi.

### B. Komponen, Fitur, dan RFC yang Terdampak
- **Fitur Terdampak**: `F1` (Halaman Beranda & Brand Profile) dan `F2` (Katalog Menu Publik & Pengelompokan Kategori).
- **Aturan Terdampak**: `UI-1` di `RULES.md`.
- **RFC Terdampak**: `RFC-001` (`RFCs/RFC-001-Foundation-Auth-Catalog.md`, Bagian 1, 2.5, 4, dan `AC-10`), serta acuan nama tema pada `RFC-002` (Bagian 4) dan `RFC-003` (Bagian 4).
- **Status Pekerjaan Saat Ini**: Seluruh RFC (`RFC-001`, `RFC-002`, `RFC-003`) masih berstatus `Not started`. Belum ada kode implementasi yang perlu dibongkar.

---

## 3. Implementation Strategy

- **Rekomendasi Waktu Eksekusi**: **Implemented now** (diintegrasikan langsung ke dalam dokumen spesifikasi sebelum memulai `/implement-rfc RFC-001`).
- **Strategi Pengujian**:
  - Seluruh 33 rencana *automated feature test* di `TEST-STRATEGY.md` tetap valid 100%, termasuk pengecekan token warna (`#b22e3b`, `#fffaee`, `#e9bfd2`, `#020202`), landmark semantik (`header`, `nav`, `main`, `footer`, satu `h1`), jam operasional dari `config('gumi.operating_hours')`, lokasi dari `config('gumi.location')`, dan tautan CTA `"Lihat Menu"` menuju `/menus`.

---

## 4. Documentation Updates Required (Upon Acceptance)

Setelah perubahan ini disetujui (**Accepted**), pembaruan berikut akan diterapkan secara serentak dalam satu langkah:

1. **`PRD.md`**:
   - **Overview** & **NFR-4**: Mengubah deskripsi arah visual dari *"Urban / Neo-Brutalist (*border* tegas dan *hard offset shadow*)"* menjadi *"Playful Pop-Editorial / Retro Coffee Club (*full-width color-blocked bands*, tipografi *display all-caps* tebal/miring, motif grafis *sunburst/starburst*, dan tombol persegi kontras tinggi)"* dengan tetap mempertahankan palet warna `#b22e3b`, `#fffaee`, `#e9bfd2`, dan `#020202` serta syarat kontras WCAG AA (`#020202` di atas `#fffaee`/`#e9bfd2`, dan `#fffaee` di atas `#b22e3b`/`#020202`).
   - **Decisions (Decision 7)**: Mencatat pembaruan `Decision 7` berdasarkan `CR-001` (apa yang berubah, alasan perubahan mengikuti referensi *Soletta Coffee Club*, dan apa yang digantikan sambil mempertahankan palet warna asli).
2. **`FEATURES.md`**:
   - **Product Overview** & **F1** (Judul & Acceptance Criteria #3): Memperbarui arah visual `F1` ke gaya *Playful Pop-Editorial / Retro Coffee Club* dengan komposisi blok warna horizontal, tipografi *bold/italic all-caps*, dan motif *sunburst/starburst* menggunakan palet `#b22e3b`, `#fffaee`, `#e9bfd2`, dan `#020202` (tanpa mengubah ID fitur `F1`–`F13`).
3. **`RULES.md`**:
   - **Technology Stack (`tailwindcss`)** & **Rule `UI-1`**: Memperbarui definisi `UI-1` agar mewajibkan gaya *Playful Pop-Editorial / Retro Coffee Club* (*color-blocked bands*, tipografi *display* tegas, motif *sunburst/starburst*, tombol persegi solid/outline) dengan palet `#b22e3b`, `#fffaee`, `#e9bfd2`, `#020202` dan kontras WCAG AA (tanpa mengubah ID aturan `UI-1`).
4. **`RFCS.md`**:
   - Memperbarui nama fitur `F1` pada tabel *Feature-to-RFC Traceability Matrix* agar selaras dengan `FEATURES.md`. Status `RFC-001`, `RFC-002`, dan `RFC-003` tetap `Not started`.
5. **`RFCs/RFC-001-Foundation-Auth-Catalog.md`** (serta referensi nama tema di `RFC-002` & `RFC-003`):
   - **Section 1, Section 2.5, Section 4, dan AC-10**: Merinci komposisi seksi halaman Beranda (`GET /`) dan Katalog (`GET /menus`) sesuai struktur gambar referensi (*Top Announcement Bar*, *Solid Navbar*, *Hero dengan Lingkaran Aksen & Starburst Badge*, *Ticker Strip*, *Featured Menu Showcase "Meet Your Morning People"*, *Category Selector Banner "What's Your Type?"*, *50/50 Split Story & Info Kedai*, *Pre-Footer Sunburst Tagline*, dan *Solid Footer*) menggunakan palet `#b22e3b`, `#fffaee`, `#e9bfd2`, dan `#020202`.
6. **`TEST-STRATEGY.md`**:
   - Memperbarui deskripsi teks pada `RFC-001 AC-10` agar merujuk pada token warna tema *Playful Pop-Editorial* (`#b22e3b`, `#fffaee`, `#e9bfd2`, `#020202`).
7. **Kode Aplikasi yang Akan Terdampak Saat `/implement-rfc`**:
   - `resources/views/layouts/*`, `resources/views/welcome.blade.php` (atau `home.blade.php`), `resources/views/menus/index.blade.php`, `resources/views/auth/*`, `resources/views/cart/*`, `resources/views/checkout/*`, `resources/views/orders/*`, `resources/views/admin/*`, `tailwind.config.js`, dan `resources/css/app.css`.
