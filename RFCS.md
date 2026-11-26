# RFC Index & Implementation Roadmap: Gumi Coffee

## Overview

Dokumen `RFCS.md` ini adalah indeks utama untuk seluruh Request for Comments (RFC) pada proyek **Gumi Coffee** (`web app`, skala `Small`). Sesuai skala proyek, seluruh **13 fitur (`F1` s/d `F13`, terdiri dari 12 Must Have dan 1 Should Have)** dibagi ke dalam **3 RFC berurutan** yang masing-masing dapat diimplementasikan secara utuh setelah pendahulunya selesai.

Setiap RFC diimplementasikan dengan menjalankan perintah **`/implement-rfc <id>`** (contoh: `/implement-rfc RFC-001`) dan ditinjau dengan **`/review-rfc <id>`**.

---

## 1. Master RFC Table

| RFC ID | Title | File | Features | Complexity | Status | Cold Read |
|---|---|---|---|---|---|---|
| **RFC-001** | Project Foundation, Authentication, Schema & Public Catalog | `RFCs/RFC-001-Foundation-Auth-Catalog.md` | `F1`, `F2`, `F3`, `F4` | Low | Not started | Passed (`cold-reads/RFC-001-round-2.md`) |
| **RFC-002** | Session Cart, Takeaway Checkout, Order State Machine & Customer Orders | `RFCs/RFC-002-Cart-Checkout-Customer-Orders.md` | `F5`, `F6`, `F7`, `F8`, `F9` | Medium | Not started | Passed (`cold-reads/RFC-002-round-2.md`) |
| **RFC-003** | Admin Order & Menu Management and Vercel Serverless Configuration | `RFCs/RFC-003-Admin-Management-Vercel-Deploy.md` | `F10`, `F11`, `F12`, `F13` | Medium | Not started | Passed (`cold-reads/RFC-003-round-1.md`) |

*(Catatan: Seluruh fitur Must Have `F1` s/d `F12` dan fitur Should Have `F13` telah dialokasikan ke dalam RFC di atas; tidak ada fitur dari `FEATURES.md` yang ditunda).*

---

## 2. Dependency Graph & Predecessor/Successor Matrix

### Directed Dependency Graph (Textual)
```text
[RFC-001: Foundation, Auth, Schema & Public Catalog (F1, F2, F3, F4)]
       |
       v
[RFC-002: Session Cart, Checkout, State Machine & Customer Orders (F5, F6, F7, F8, F9)]
       |
       v
[RFC-003: Admin Order & Menu Management + Vercel Config (F10, F11, F12, F13)]
```

### Predecessor & Successor Table

| RFC ID | Predecessors (`Depends on`) | Successors | Rules Applied |
|---|---|---|---|
| **RFC-001** | `none` | `RFC-002` | `ARCH-2`, `DATA-3`, `SEC-2`, `UI-1`, `UI-2`, `TEST-1` |
| **RFC-002** | `RFC-001` | `RFC-003` | `ARCH-1`, `ARCH-2`, `DATA-1`, `DATA-2`, `DATA-3`, `SEC-1`, `SEC-3`, `UI-1`, `UI-2`, `TEST-1` |
| **RFC-003** | `RFC-002` | `none` | `ARCH-1`, `DATA-1`, `DATA-3`, `SEC-1`, `SEC-3`, `UI-1`, `UI-2`, `TEST-1`, `OPS-1` |

---

## 3. Feature-to-RFC Traceability Matrix

| Feature ID | Feature Name | Priority | Assigned RFC |
|---|---|---|---|
| **F1** | Halaman Beranda & Brand Profile Urban | Must | `RFC-001` |
| **F2** | Katalog Menu Publik & Pengelompokan Kategori | Must | `RFC-001` |
| **F3** | Autentikasi Laravel Breeze, Role Pengguna & Database Seeder | Must | `RFC-001` |
| **F4** | User Profile Pelanggan Tanpa Fitur Hapus Akun | Must | `RFC-001` |
| **F5** | Proteksi Rute Admin (`EnsureUserIsAdmin`) & Isolasi Pesanan (`OrderPolicy`) | Must | `RFC-002` |
| **F6** | Keranjang Belanja Session (Tamu & Pelanggan) dengan Redirect Login ke Checkout | Must | `RFC-002` |
| **F7** | Checkout Takeaway, Validasi Jam Buka & Transaksi Pembuatan `order_code` | Must | `RFC-002` |
| **F8** | Simulasi Pembayaran QRIS Dummy & Pembatalan Pesanan oleh Pelanggan | Must | `RFC-002` |
| **F9** | Riwayat & Detail Status Pesanan Pelanggan | Must | `RFC-002` |
| **F10** | Dashboard Pesanan Admin, Pencarian `order_code` & Penandaan Lunas Tunai (`cash`) | Must | `RFC-003` |
| **F11** | State Machine Transisi Status & Pembatalan Pesanan Terpusat oleh Admin | Must | `RFC-003` |
| **F12** | Manajemen Katalog Menu (CRUD) & Quick-Toggle Ketersediaan oleh Admin | Must | `RFC-003` |
| **F13** | Konfigurasi Runtime Serverless Vercel | Should | `RFC-003` |

---

## 4. Implementation Roadmap & Release Checklist

1. **Tahap 1 (`/implement-rfc RFC-001`)**: Menyiapkan aplikasi Laravel, konfigurasi `Asia/Jakarta` & `config/gumi.php`, migrasi keempat tabel inti (`users`, `menus`, `orders`, `order_items`), `DatabaseSeeder` dengan aset gambar statis di `public/`, autentikasi Breeze tanpa hapus akun, halaman Beranda (`/`), dan Katalog Menu (`/menus`).
2. **Tahap 2 (`/implement-rfc RFC-002`)**: Mengimplementasikan keranjang belanja session (termasuk alur tamu login menuju `/checkout`), transaksi checkout & pembuatan `order_code` (`GM-0001`), metode *state machine* terpusat pada `Order` dengan `lockForUpdate()`, middleware `EnsureUserIsAdmin`, `OrderPolicy`, halaman pesanan pelanggan (`/orders`, `/orders/{order}`), simulasi bayar `qris_dummy`, dan pembatalan pesanan oleh pelanggan.
3. **Tahap 3 (`/implement-rfc RFC-003`)**: Mengimplementasikan panel pesanan Admin (`/admin/orders`, filter status, pencarian `order_code`, tandai lunas `cash`, transisi status & pembatalan pesanan `unpaid`), CRUD Menu Admin (`/admin/menus` + *quick-toggle* `is_available`), serta file `vercel.json` dan `api/index.php`.

### Manual Release Checklist (Post-Implementation)
- Menghubungkan proyek Vercel ke database relasional eksternal (misal Neon PostgreSQL atau TiDB MySQL) sesuai `OQ-1` pada `PRD.md` dan menjalankan `php artisan migrate --seed` terhadap database produksi tersebut.
- Memverifikasi tampilan visual di peramban nyata pada ukuran layar Mobile (`375px`), Tablet (`768px`), dan Desktop (`1280px+`).

---

## Open conflicts

Tidak ditemukan konflik antar-artefak (`PRD.md`, `PRD-REVIEW.md`, `FEATURES.md`, `RULES.md`) maupun pertanyaan terbuka berstatus *High impact* yang menghalangi implementasi RFC.
