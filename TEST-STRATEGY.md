# Test Strategy & Per-RFC Test Plan: Gumi Coffee

## Baseline & Project Context

- **Product Type**: `web app` (Laravel Blade, Tailwind CSS, Alpine.js), Size: **`Small`**.
- **Step 0 Baseline**: Proyek ini adalah proyek baru (*greenfield*) yang seluruh RFC-nya (`RFC-001`, `RFC-002`, `RFC-003`) masih berstatus `Not started`. Belum ada kode aplikasi maupun *test suite* yang ada di direktori kerja saat rencana ini ditulis (`0 existing tests`). Sesuai panduan, kolom `Exists?` tidak disertakan karena seluruh pengujian pada dokumen ini berstatus *proposed* untuk diimplementasikan bersama masing-masing RFC.
- **Standar Pengujian (`TEST-1` dari `RULES.md`)**: Seluruh pengujian otomatis dijalankan menggunakan **PHPUnit** dengan database SQLite *in-memory* (`:memory:`) dan trait `Illuminate\Foundation\Testing\RefreshDatabase`.

---

## Summary of Planned Tests

| RFC ID | Title | Criteria Count | Planned Automated Tests | Manual Checks |
|---|---|---|---|---|
| **RFC-001** | Project Foundation, Authentication, Schema & Public Catalog | 10 (`AC-1` s/d `AC-10`) | 11 | 1 (Visual viewport layout) |
| **RFC-002** | Session Cart, Takeaway Checkout, Order State Machine & Customer Orders | 10 (`AC-1` s/d `AC-10`) | 12 | 0 |
| **RFC-003** | Admin Order & Menu Management and Vercel Serverless Configuration | 9 (`AC-1` s/d `AC-9`) | 10 | 0 |
| **Total** | **Seluruh RFC** | **29** | **33** | **1** |

---

## RFC-001: Project Foundation, Authentication, Schema & Public Catalog

| Criterion | Test | Level |
|---|---|---|
| **AC-1** | `tests/Feature/FoundationAndCatalogTest.php`: assert `config('app.timezone') === 'Asia/Jakarta'`, set `config(['gumi.operating_hours.open' => '09:00', 'gumi.operating_hours.close' => '21:30'])`, panggil `GET /`, dan pastikan string `'09:00'` serta `'21:30'` tampil di HTML beranda (gagal jika jam buka ditulis *hardcoded* di Blade). | integration |
| **AC-2** | `tests/Feature/FoundationAndCatalogTest.php`: panggil `GET /`, assert HTTP `200`, pastikan memuat teks cerita brand, nilai `config('gumi.location')`, dan elemen `<a` dengan teks `"Lihat Menu"` serta atribut `href` menuju `/menus`. | integration |
| **AC-3** | `tests/Feature/DatabaseIntegrityTest.php`: buat `User`, `Menu`, `Order`, dan `OrderItem`; verifikasi relasi `User::orders()`, `Order::user()`, `Order::items()`, `OrderItem::order()`, dan `OrderItem::menu()`, pastikan `Schema::hasColumns('orders', ['order_type', 'table_id'])` bernilai `false`; uji properti anti-kehilangan riwayat (`G-3`) dengan memanggil `$user->delete()` saat user memiliki order (wajib gagal melempar `QueryException` akibat `restrictOnDelete()`) dan memanggil `$menu->delete()` saat menu direferensikan `order_items` (baris `order_items` wajib tetap ada dengan `menu_id === null` serta `menu_name`, `unit_price`, dan `subtotal` utuh). | integration |
| **AC-4** | `tests/Feature/DatabaseSeederTest.php`: jalankan `$this->seed(DatabaseSeeder::class)`, pastikan `User::where('email', 'admin@gumicoffee.test')->first()` memiliki `role === 'admin'`, `User::where('email', 'customer@gumicoffee.test')->first()` memiliki `role === 'customer'`, jumlah `Menu` minimal 6 yang mencakup kategori `'Coffee'`, `'Non-Coffee'`, `'Pastry'` (minimal 5 `is_available = true` dan minimal 1 `is_available = false`), serta setiap `image_path` yang tidak null menunjuk ke file yang benar-benar ada di `public_path($menu->image_path)`. | integration |
| **AC-5** | `tests/Feature/FoundationAndCatalogTest.php`: buat 2 menu (`image_path` terisi vs `image_path = null`), panggil `GET /menus` sebagai tamu (tanpa login), assert HTTP `200`, pastikan kategori, nama, deskripsi, harga terformat Rupiah, gambar menu pertama, dan elemen placeholder untuk menu kedua tampil di respons. | integration |
| **AC-6** | `tests/Feature/FoundationAndCatalogTest.php`: buat menu dengan `is_available = false`, panggil `GET /menus`, pastikan badge `"Habis"` / `"Tidak Tersedia"` muncul dan tidak ada tombol submit aktif untuk menu tersebut. | integration |
| **AC-7** | `tests/Feature/Auth/RegistrationTest.php`: kirim `POST /register` dengan payload valid ditambah injeksi field `'role' => 'admin'`; pastikan redirect ke `/menus` dan baris user di database tetap memiliki `role === 'customer'` (gagal jika `role` terkena *mass-assignment*). | security |
| **AC-8** | `tests/Feature/Auth/AuthenticationTest.php` & `tests/Feature/ProfileTest.php`: verifikasi `POST /login` (redirect default ke `/menus` saat tanpa `url.intended`), `GET /dashboard` redirect ke `/menus`, `POST /logout`, `PATCH /profile` (ubah nama & email), dan `PUT /password` (ubah password). | integration |
| **AC-9** | `tests/Feature/ProfileTest.php`: login sebagai customer, panggil `GET /profile` dan pastikan tidak memuat teks/form hapus akun (`profile.destroy`); kirim `DELETE /profile`, pastikan status respons `404` atau `405` dan baris user tetap ada di tabel `users`. | security |
| **AC-10** | `tests/Feature/FoundationAndCatalogTest.php` (Otomatis) & Pengecekan Visual Viewport (Manual): secara otomatis assert respons `GET /` dan `GET /menus` memuat `<html lang="id">`, `<header`, `<nav`, `<main`, `<footer`, tepat satu `<h1`, serta token kelas warna tema Playful Pop-Editorial (`#b22e3b`, `#fffaee`, `#e9bfd2`, `#020202`); pengecekan ketiadaan *horizontal overflow* di lebar `375px`, `768px`, dan `1280px` diverifikasi pada browser/DevTools karena PHPUnit HTTP test tidak menjalankan layout engine CSS. | integration / manual |

### Fixtures & Notes for RFC-001
- Menggunakan `DatabaseSeeder` atau helper pembuatan model (`User`, `Menu`, `Order`, `OrderItem`) pada setiap test dengan `RefreshDatabase`.
- Tidak membutuhkan mock layanan eksternal.

---

## RFC-002: Session Cart, Takeaway Checkout, Order State Machine & Customer Orders

| Criterion | Test | Level |
|---|---|---|
| **AC-1** | `tests/Feature/CartTest.php`: uji `POST /cart/items` untuk menu `is_available = true` (sukses masuk `session('cart')`), batas nilai `quantity = 0` dan `quantity = 100` (gagal dengan error validasi `quantity`), akumulasi dua kali tambah hingga `> 99` (gagal dengan error `quantity`), tambah menu dengan `is_available = false` (gagal dengan error `cart`), `PATCH /cart/items/{menuId}` ke kuantitas `3`, dan `DELETE /cart/items/{menuId}` (termasuk saat baris menu sudah dihapus dari tabel `menus`, tetap berhasil menghapus item dari session tanpa `404`). | integration |
| **AC-2** | `tests/Feature/CartTest.php`: sebagai tamu (belum login), tambahkan menu ke keranjang via `POST /cart/items`, panggil `GET /checkout` (assert redirect ke `/login`), kirim `POST /login` dengan kredensial customer, pastikan response redirect ke `/checkout` dan isi `session('cart')` masih menyimpan item beserta kuantitas sebelumnya. | integration |
| **AC-3** | `tests/Feature/CheckoutTest.php`: login sebagai customer dengan keranjang terisi, panggil `GET /checkout`, pastikan tampil teks **Takeaway / Pick-up**, nilai `config('gumi.estimated_pickup_text')`, dan pilihan `cash` / `qris_dummy`; kirim `POST /checkout` dengan `notes` sepanjang `501` karakter (gagal dengan error validasi `notes`) dan dengan `500` karakter (lolos validasi `notes`). | integration |
| **AC-4** | `tests/Feature/CheckoutTest.php`: atur `config(['gumi.operating_hours.open' => '08:00', 'gumi.operating_hours.close' => '22:00'])`; bekukan waktu dengan `Carbon::setTestNow(Carbon::createFromTimeString('22:01:00', 'Asia/Jakarta'))` dan `07:59:00`, kirim `POST /checkout`, pastikan gagal dengan error validasi pada kunci `checkout` dan `Order::count() === 0`. | integration |
| **AC-5** | `tests/Feature/CheckoutTest.php`: uji properti atomisitas transaksi (`G-2`): masukkan Menu A (`is_available = true`) dan Menu B (`is_available = true`, nama `'Kopi Gula Aren'`) ke keranjang, lalu ubah Menu B di DB menjadi `is_available = false` (dan skenario kedua: hapus Menu B dari DB) tepat sebelum `POST /checkout`; pastikan transaksi melakukan *rollback* penuh (`Order::count() === 0`, `OrderItem::count() === 0`), `session('cart')` tidak hilang, dan pesan error validasi `checkout` memuat string `'Kopi Gula Aren'`. | integration |
| **AC-6** | `tests/Feature/CheckoutTest.php`: bekukan waktu di dalam jam buka (`10:00` WIB), masukkan 2 menu (`Rp 20.000` x 2 dan `Rp 15.000` x 1) ke keranjang, kirim `POST /checkout` sambil menyuntikkan payload palsu `'total' => 1000, 'unit_price' => 500`; pastikan `Order` tersimpan dengan `total === 55000`, `status === 'pending'`, `payment_status === 'unpaid'`, `order_code` berformat `'GM-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)`, baris `order_items` menyimpan snapshot `menu_name`, `unit_price`, `quantity`, `subtotal`, serta `session('cart')` kosong. | security / integration |
| **AC-7** | `tests/Feature/CustomerOrderTest.php`: buat pesanan `qris_dummy` (`pending`, `unpaid`), panggil `GET /orders/{order}` dan pastikan memuat label **"SIMULASI"** serta tombol **"Saya sudah bayar"**; kirim `POST /orders/{order}/pay`, pastikan `payment_status === 'paid'` dan `paid_at !== null`; uji penolakan `POST /orders/{order}/pay` jika `payment_method = 'cash'`, jika `status = 'preparing'`/`'cancelled'`, atau jika sudah `'paid'` (gagal dengan error `payment_status`). | integration |
| **AC-8** | `tests/Feature/CustomerOrderTest.php`: buat pesanan (`pending`, `unpaid`), kirim `POST /orders/{order}/cancel` sebagai pemilik (sukses mengubah `status === 'cancelled'`); uji properti anti-konflik pembayaran vs pembatalan (`G-2` / `P1`): (1) pada pesanan `qris_dummy` yang baru saja dibayar (`POST /orders/{order}/pay` -> `paid`), percobaan `POST /orders/{order}/cancel` wajib ditolak dengan error `status` dan pesanan tetap `pending` + `paid`; (2) pada pesanan yang baru saja dibatalkan (`POST /orders/{order}/cancel` -> `cancelled`), percobaan `POST /orders/{order}/pay` wajib ditolak dengan error `payment_status` dan pesanan tetap `cancelled` + `unpaid`; (3) pesanan dengan status `preparing`, `ready`, atau `completed` wajib ditolak saat pelanggan mencoba membatalkan. | integration |
| **AC-9** | `tests/Feature/CustomerOrderTest.php`: buat Customer A dan Customer B; saat Customer B mengakses `GET /orders/{orderA}`, `POST /orders/{orderA}/pay`, atau `POST /orders/{orderA}/cancel` milik Customer A, ketiganya wajib mengembalikan HTTP `403 Forbidden`; halaman `GET /orders` milik Customer B hanya menampilkan pesanan Customer B. | security |
| **AC-10** | `tests/Feature/AdminAccessTest.php`: panggil `GET /admin` sebagai tamu (assert redirect ke `/login`), panggil sebagai `customer` (assert HTTP `403 Forbidden`), dan panggil sebagai `admin` (assert bukan `403`/bukan redirect ke `/login`). | security |

### Fixtures & Prerequisites for RFC-002
- Prasyarat: Skema tabel dan model dari `RFC-001` telah aktif.
- Menggunakan `Illuminate\Support\Carbon::setTestNow()` untuk menguji batas jam operasional (`Asia/Jakarta`) secara deterministik kapan pun test dijalankan.

---

## RFC-003: Admin Order & Menu Management and Vercel Serverless Configuration

| Criterion | Test | Level |
|---|---|---|
| **AC-1** | `tests/Feature/AdminOrderManagementTest.php`: buat beberapa pesanan dengan status berbeda dan kode `'GM-0042'`; login sebagai `admin`, uji `GET /admin/orders?status=preparing` (hanya menampilkan pesanan `preparing`), serta uji `GET /admin/orders?search=0042` dan `GET /admin/orders?search=gm-0042` (keduanya menampilkan pesanan `'GM-0042'`); pastikan akses oleh `customer` ke seluruh endpoint `/admin/orders*` ditolak `403`. | integration |
| **AC-2** | `tests/Feature/AdminOrderManagementTest.php`: sebagai `admin`, kirim `POST /admin/orders/{order}/mark-paid` pada pesanan `cash` (`unpaid`) di status `pending`, `preparing`, dan `ready` (ketiganya sukses mengubah `payment_status = 'paid'` dan mengisi `paid_at`); pastikan ditolak dengan error `payment_status` apabila pesanan berstatus `cancelled`, `completed`, menggunakan `qris_dummy`, atau sudah `paid`. | integration |
| **AC-3** | `tests/Feature/AdminOrderStateMachineTest.php`: sebagai `admin`, uji transisi maju berurutan `pending -> preparing -> ready -> completed` pada pesanan yang sudah `paid` (sukses); uji dan pastikan seluruh loncatan status (`pending -> ready`, `pending -> completed`, `preparing -> completed`) dan transisi mundur (`ready -> preparing`, `completed -> ready`, `cancelled -> pending`) ditolak dengan error validasi `status` tanpa mengubah status di database. | unit / integration |
| **AC-4** | `tests/Feature/AdminOrderStateMachineTest.php`: sebagai `admin`, uji `pending -> preparing` untuk pesanan `cash` yang `unpaid` (sukses) vs pesanan `qris_dummy` yang `unpaid` (ditolak dengan error `status`); uji `ready -> completed` untuk pesanan `cash` yang masih `unpaid` (ditolak dengan error `status`). | integration |
| **AC-5** | `tests/Feature/AdminOrderStateMachineTest.php`: uji properti bebas *deadlock* dan larangan batal setelah lunas (`G-2`): sebagai `admin`, batalkan pesanan `unpaid` dari status `pending`, `preparing`, dan **`ready`** (kasus pesanan `cash` tidak diambil pelanggan: `ready + unpaid -> cancelled` sukses); pastikan pembatalan oleh Admin selalu ditolak dengan error `status` pada pesanan yang sudah `payment_status = 'paid'` (baik di status `pending`, `preparing`, `ready`, maupun `completed`). | integration |
| **AC-6** | `tests/Feature/AdminMenuManagementTest.php`: sebagai `admin`, kirim `POST /admin/menus` dua kali dengan nama yang sama `'Kopi Susu Gumi'` dan `price = 25000`; pastikan keduanya tersimpan dengan `slug` unik (`'kopi-susu-gumi'` dan `'kopi-susu-gumi-2'`); uji `PUT /admin/menus/{menu}` untuk memperbarui menu; pastikan input dengan `price = 500` (`< 1000`) atau `category = 'Invalid'` ditolak dengan error validasi. | integration |
| **AC-7** | `tests/Feature/AdminMenuManagementTest.php`: sebagai `admin`, kirim `PATCH /admin/menus/{menu}/toggle-availability` pada menu dengan `is_available = true`, pastikan berubah menjadi `false` di database dan ditampilkan sebagai tidak tersedia pada `GET /menus`; panggil sekali lagi dan pastikan kembali menjadi `true`. | integration |
| **AC-8** | `tests/Feature/AdminMenuManagementTest.php`: buat pesanan pelanggan yang memuat Menu X (`menu_name = 'Americano Dingin'`, `unit_price = 22000`), lalu sebagai `admin` hapus Menu X melalui `DELETE /admin/menus/{menuX}`; pastikan Menu X hilang dari tabel `menus`, namun baris `order_items` pesanan tersebut tetap ada dengan `menu_id === null`, `menu_name === 'Americano Dingin'`, dan `unit_price === 22000`, serta halaman `GET /orders/{order}` pelanggan tetap merender pesanan tersebut tanpa error (`200 OK`). | integration |
| **AC-9** | `tests/Feature/VercelConfigTest.php`: verifikasi bahwa file `vercel.json` dan `api/index.php` ada di root proyek, `vercel.json` berisi JSON valid yang memuat konfigurasi `VIEW_COMPILED_PATH`, `APP_CONFIG_CACHE`, `APP_ROUTES_CACHE` ke `/tmp`, `LOG_CHANNEL` ke `stderr`, serta `SESSION_DRIVER` ke `cookie`, dan `api/index.php` menyiapkan direktori `/tmp` sebelum meneruskan eksekusi ke `public/index.php`. | integration |

### Prerequisites for RFC-003
- Prasyarat: Metode *state machine* terpusat pada `App\Models\Order` (`markCashPaidByAdmin()`, `transitionStatusByAdmin()`) dan middleware `EnsureUserIsAdmin` dari `RFC-002` telah tersedia.

---

## Manual Testing Gaps

1. **Verifikasi Responsivitas & Tidak Ada Horizontal Overflow (`RFC-001 AC-10`)**:
   - Karena pengujian fitur PHPUnit hanya memverifikasi struktur DOM dan kelas CSS tanpa melakukan *layout rendering* grafis, pengecekan visual pada ukuran viewport Mobile (`375px`), Tablet (`768px`), dan Desktop (`1280px+`) dilakukan pada peramban/Chrome DevTools saat implementasi dan sebelum rilis.
2. **Smoke Test Deployment Produksi di Vercel (`RFC-003 AC-9` & Release Checklist)**:
   - Memverifikasi koneksi dari *serverless function* Vercel ke database relasional eksternal (Neon PostgreSQL / TiDB MySQL sesuai `OQ-1` di `PRD.md`) setelah deployment.
