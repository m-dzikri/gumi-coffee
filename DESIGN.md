# Design Direction & Liveliness Specification: Gumi Coffee Club

## 1. Design Read (`R-37` & Part 3)

> Reading this as: lightweight brand profile and takeaway coffee ordering web app for urban coffee customers and baristas in Bandung, in a **Playful Pop-Editorial / Retro Coffee Club** visual language (inspired by *Soletta Coffee Club*), dial **ENERGY 3 / RHYTHM 3 / MOTION 1**.

- **Dial**: `ENERGY 3 / RHYTHM 3 / MOTION 1`
  - **ENERGY 3 (Bold)**: Pita blok warna penuh (*full-width color-blocked bands*), tipografi *display* kapital tebal (`font-black uppercase`) dengan kata kunci miring (*italic*), dan kontras warna tajam antara krem hangat (`#fffaee`), merah crimson (`#b22e3b`), pink retro (`#e9bfd2`), serta hitam pekat (`#020202`).
  - **RHYTHM 3 (Varied)**: Setiap bagian halaman beranda memiliki komposisi berbeda dan tidak berulang: (1) *asymmetric 7/5 hero* dengan lingkaran latar datar & stiker miring, (2) pita *ticker* horizontal bermotif matahari, (3) pameran 3 menu unggulan dengan sorotan khusus pada racikan utama (*Kopi Susu Gumi*), (4) blok kontras gelap `#020202` pemilih kategori *"What's Your Type?"*, dan (5) blok cerita *50/50 split* penuh (*full-bleed*).
  - **MOTION 1 (Calm / Functional)**: Gerak dibatasi hanya pada *hover/active states* tombol (`active:translate-y-0.5`), skala halus gambar menu saat *hover* (`group-hover:scale-105`), dan indikator *loading* pada pengiriman form, serta berhenti sepenuhnya saat `prefers-reduced-motion: reduce` aktif (`R-19`, `R-39`).

---

## 2. Color Palette & Verified WCAG AA Pairings (`R-25`, `R-29`)

Palet aktif dibatasi ketat pada **3 warna inti + 1 warna aksen** (`R-29`):
- **Warm Cream (`#fffaee`)**: Kanvas utama halaman dan warna teks terang di atas latar gelap.
- **Deep Ink (`#020202`)**: Warna teks utama, garis tepi tegas (`border-2`), dan blok kontras gelap.
- **Crimson Red (`#b22e3b`)**: Warna identitas utama Gumi Coffee untuk navbar, footer, CTA utama, dan judul *display*.
- **Retro Pink (`#e9bfd2`)**: Warna aksen untuk pita pengumuman, *header band* halaman dalam, latar lingkaran hero, dan kartu ringkasan pesanan.

### Tabel Verifikasi Rasio Kontras (`contrast-check.py`)
| Pasangan Warna (Teks di atas Latar) | Rasio Kontras | Teks Normal (`>= 4.5:1`) | Teks Besar `18px+` (`>= 3.0:1`) | Penggunaan yang Diizinkan |
|---|---|---|---|---|
| `#020202` di atas `#fffaee` | **19.92:1** | PASS | PASS | Teks isi, judul, label form, dan border utama |
| `#fffaee` di atas `#020202` | **19.92:1** | PASS | PASS | Teks pada blok gelap, menu mobile, dan badge status |
| `#020202` di atas `#e9bfd2` | **12.69:1** | PASS | PASS | Seluruh teks normal & subjudul di atas pita/kartu pink |
| `#e9bfd2` di atas `#020202` | **12.69:1** | PASS | PASS | Aksen teks dan ilustrasi garis di atas blok hitam |
| `#fffaee` di atas `#b22e3b` | **6.02:1** | PASS | PASS | Seluruh teks navbar, footer, tombol utama `.pop-btn-crimson`, dan alert |
| `#b22e3b` di atas `#fffaee` | **6.02:1** | PASS | PASS | Judul bagian, harga, dan label kategori di atas kanvas krem |
| `#020202/60` (`#676560`) di atas `#fffaee` | **5.59:1** | PASS | PASS | Placeholder input `.pop-input` dan teks status stok habis |
| `#b22e3b` di atas `#e9bfd2` | **3.84:1** | **FAIL** | **PASS** | **Hanya** untuk judul *display* berukuran besar (`text-2xl` s/d `text-6xl`, `>= 24px`); dilarang untuk teks normal `< 18px` |

---

## 3. One-Line Reasons for Every Major Visual Decision (`R-31`)

1. **Mengapa palet warna `#b22e3b`, `#fffaee`, `#e9bfd2`, dan `#020202`? (`R-01`, `R-29`)**
   Menghadirkan karakter hangat kedai kopi retro-modern yang kontras tinggi di layar ponsel luar ruangan sekaligus menjaga kepatuhan kontras WCAG AA tanpa gradien dekoratif.
2. **Mengapa tata letak pita warna horizontal penuh (*full-width color-blocked bands*)? (`R-05`)**
   Memisahkan narasi beranda (Hero, Ticker Karakter, Menu Unggulan, Pemilih Kategori, dan Cerita Kedai) melalui ritme blok warna yang bervariasi (`RHYTHM 3`) sesuai referensi *Soletta Coffee Club*.
3. **Mengapa tipografi sans-serif `Figtree` dengan judul `font-black uppercase` dan aksen `italic`? (`R-06`)**
   Memberikan suara editorial poster klub kopi yang lantang pada judul (`GOOD COFFEE. ZERO SNOOZE.`) sambil menjaga keterbacaan tinggi pada deskripsi menu, harga Rupiah, dan kode pesanan (`GM-0001`).
4. **Mengapa jarak ruang (*spacing*) dan sudut persegi tajam (`rounded-none`, `border-2 border-[#020202]`)? (`R-11`, `R-12`)**
   Menegaskan struktur *pop-editorial* cetak tanpa efek kartu melayang (*no floating soft shadows*) dan membedakan tombol aksi persegi dari lingkaran latar produk (`rounded-full`) di hero.
5. **Mengapa kartu menu unggulan pertama diberi bingkai `#020202` dan label `RACIKAN UTAMA GUMI`? (`R-14`)**
   Membedakan menu andalan utama kedai (*Kopi Susu Gumi*) dari dua menu pendamping agar pameran menu memiliki hierarki visual yang jelas dan bukan kartu *copy-paste* yang seragam.
6. **Mengapa motif grafis *Rising Sun / Sunburst* dan ilustrasi SVG cangkir/ketel kustom? (`R-04`, `R-22`)**
   Menjadi *identity motif* berulang yang melambangkan ritual kopi pagi yang diseduh segar di Bandung, menggantikan ikon pustaka generik.
7. **Mengapa tema terang tetap (*fixed light mode*) tanpa *dark mode toggle*? (`R-21`)**
   Identitas visual *Playful Pop-Editorial* Gumi Coffee bertumpu pada kontras blok warna kertas krem hangat (`#fffaee`), merah crimson (`#b22e3b`), dan hitam tinta (`#020202`) layaknya cetakan majalah/kemasan kopi fisik.
