# RFC-002: Session Cart, Takeaway Checkout, Order State Machine & Customer Orders

**Features**: F5, F6, F7, F8, F9
**Depends on**: RFC-001
**Rules**: ARCH-1, ARCH-2, DATA-1, DATA-2, DATA-3, SEC-1, SEC-3, UI-1, UI-2, TEST-1
**Complexity**: Medium

## 1. Summary

RFC ini mengimplementasikan alur transaksi pelanggan dari keranjang belanja hingga pengelolaan pesanan milik pelanggan, sekaligus meletakkan fondasi *state machine* pesanan terpusat dan otorisasi (`EnsureUserIsAdmin` & `OrderPolicy`):
- Keranjang belanja berbasis **Laravel Session** yang dapat digunakan oleh tamu (belum login) maupun pelanggan (sudah login), dilengkapi form tambah ke keranjang pada halaman katalog `/menus`, serta tetap bertahan setelah tamu login untuk melanjutkan ke halaman Checkout.
- Proses **Checkout Takeaway** di dalam satu transaksi database (`DB::transaction()`) dengan validasi jam buka (`Asia/Jakarta`), pengecekan ulang `is_available`, kalkulasi harga murni di server, pembuatan `order_code` (`GM-0001`) dari ID pesanan, dan penyimpanan snapshot `order_items`.
- Mekanisme terpusat untuk transisi status pesanan, pembayaran, dan pembatalan pada model `Order` yang menggunakan `DB::transaction()` dan `lockForUpdate()`.
- Halaman Riwayat Pesanan, Detail Pesanan, simulasi pembayaran `qris_dummy` (**"SIMULASI"** + tombol **"Saya sudah bayar"**), dan pembatalan pesanan oleh pelanggan yang dilindungi oleh `OrderPolicy`.

---

## 2. Technical Approach & Architecture

### 2.1 Session Cart Structure & Guest-to-Checkout Flow (`F6`)
- **Session Key**: `session('cart', [])` menyimpan array asosiatif dengan kunci integer `menu_id`:
  ```php
  [
      1 => ['menu_id' => 1, 'menu_name' => 'Gumi Signature Kopi Susu', 'quantity' => 2],
      4 => ['menu_id' => 4, 'menu_name' => 'Matcha Latte', 'quantity' => 1],
  ]
  ```
- Nilai `menu_name` di dalam `session('cart')` disimpan **hanya sebagai label fallback** untuk pesan error validasi apabila baris menu tersebut dihapus dari database oleh Admin sebelum pelanggan melakukan *checkout*. Harga satuan (`price`), status ketersediaan (`is_available`), dan nama menu untuk pesanan yang valid **selalu diambil segar dari tabel `menus` di database** saat merender keranjang maupun saat transaksi *checkout*.
- **Menambah Item (`POST /cart/items`)**:
  - Input: `menu_id` (`required|integer|exists:menus,id`), `quantity` (`nullable|integer|min:1|max:99`, default `1`).
  - Jika menu berstatus `is_available = false`, tolak penambahan dengan `ValidationException::withMessages(['cart' => ...])`.
  - Jika item sudah ada di keranjang, tambahkan kuantitas baru ke kuantitas lama; jika total kuantitas item tersebut melebihi `99`, tolak dengan `ValidationException::withMessages(['quantity' => ...])` (`SEC-3`).
- **Mengubah Kuantitas (`PATCH /cart/items/{menuId}`)**:
  - Parameter `{menuId}` adalah raw integer ID (bukan implicit Eloquent route model binding).
  - Input: `quantity` (`required|integer|min:1|max:99`).
  - Memverifikasi bahwa `{menuId}` ada di dalam `session('cart')` dan baris menu masih ada di tabel `menus` dengan `is_available = true` (jika tidak memenuhi, lempar `ValidationException::withMessages(['cart' => ...])`).
- **Menghapus Item (`DELETE /cart/items/{menuId}`)**:
  - Parameter `{menuId}` adalah raw integer ID (sehingga item tetap dapat dihapus dari `session('cart')` meskipun baris menu telah dihapus dari database).
- **Guest Redirect ke Checkout**:
  - Rute `GET /checkout` dan `POST /checkout` dilindungi middleware `auth`.
  - Ketika tamu (belum login) memiliki item di `session('cart')` dan mengakses `GET /checkout`, middleware `auth` menyimpan URL `/checkout` sebagai `url.intended` di session dan mengarahkan tamu ke `/login`.
  - Saat tamu login (`POST /login`), `$request->session()->regenerate()` di Breeze mempertahankan data `session('cart')` dan `redirect()->intended(route('menus.index'))` mengarahkan pengguna langsung kembali ke `/checkout`.

### 2.2 Takeaway Checkout Transaction & `order_code` Generation (`F7`)
- **Validasi Input Request (`POST /checkout`)**:
  - `payment_method`: `required|string|in:cash,qris_dummy`
  - `notes`: `nullable|string|max:500`
- **Validasi Jam Operasional (`ARCH-2`)**:
  - Mengambil waktu sekarang dengan `now('Asia/Jakarta')->format('H:i')`.
  - Membandingkan secara inklusif terhadap `$open = config('gumi.operating_hours.open')` dan `$close = config('gumi.operating_hours.close')` (`$now >= $open && $now <= $close`).
  - Jika di luar jam buka, tolak checkout dengan `ValidationException::withMessages(['checkout' => ...])` yang menyebutkan jam operasional kedai.
- **Algoritma Transaksi Checkout (`DATA-2`, `DATA-3`)**:
  ```text
  BEGIN DB::transaction():
    1. Ambil cart dari session. Jika kosong, lempar ValidationException::withMessages(['checkout' => ...]).
    2. Query Menu::whereIn('id', array_keys($cart))->lockForUpdate()->get()->keyBy('id').
    3. Untuk setiap entry ($menuId => $cartItem) di cart:
       - $menu = $menus->get($menuId)
       - Jika !$menu atau !$menu->is_available:
         $name = $menu ? $menu->name : ($cartItem['menu_name'] ?? "Menu #{$menuId}")
         lempar ValidationException::withMessages(['checkout' => "Menu {$name} sedang tidak tersedia."])
    4. Hitung per item:
       - unit_price = $menu->price
       - subtotal = $unit_price * $cartItem['quantity']
       - Akumulasikan total = sum(subtotal)
    5. Insert Order:
       - order_code = null
       - user_id = auth()->id()
       - total = $total
       - status = 'pending'
       - payment_method = $validated['payment_method']
       - payment_status = 'unpaid'
       - paid_at = null
       - notes = $validated['notes'] ?? null
    6. Update Order di transaksi yang sama:
       - $order->order_code = 'GM-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)
       - $order->save()
    7. Insert OrderItem untuk setiap item ($order->items()->create(...)):
       - menu_id = $menu->id
       - menu_name = $menu->name
       - quantity = $cartItem['quantity']
       - unit_price = $unit_price
       - subtotal = $subtotal
  COMMIT
  Kosongkan session('cart')
  Redirect ke route('orders.show', $order)
  ```

### 2.3 Centralized Order State Machine & Concurrency Locking (`ARCH-1`, `DATA-1`)
Seluruh aturan mutasi status pesanan dan pembayaran dipusatkan pada model `App\Models\Order` (yang memiliki relasi `user(): BelongsTo` dan `items(): HasMany` dari `RFC-001`), sehingga baik aksi pelanggan di `RFC-002` maupun aksi admin di `RFC-003` melewati satu pintu validasi yang sama:

- **Konstanta Status & Pembayaran pada `App\Models\Order`**:
  - Status: `STATUS_PENDING = 'pending'`, `STATUS_PREPARING = 'preparing'`, `STATUS_READY = 'ready'`, `STATUS_COMPLETED = 'completed'`, `STATUS_CANCELLED = 'cancelled'`.
  - Payment Method: `METHOD_CASH = 'cash'`, `METHOD_QRIS_DUMMY = 'qris_dummy'`.
  - Payment Status: `PAYMENT_UNPAID = 'unpaid'`, `PAYMENT_PAID = 'paid'`.
- **Metode Mutasi Terpusat pada `App\Models\Order` (Semuanya menjalankan `DB::transaction()` + `Order::whereKey($this->id)->lockForUpdate()->firstOrFail()` dan melempar `Illuminate\Validation\ValidationException` dengan kunci error deterministik jika syarat tidak terpenuhi setelah *lock*)**:
  1. `payQrisByCustomer(): self`
     - Syarat setelah *lock*: `payment_method === 'qris_dummy'`, `status === 'pending'`, `payment_status === 'unpaid'`. Jika gagal, lempar `ValidationException::withMessages(['payment_status' => ...])`.
     - Efek: `payment_status = 'paid'`, `paid_at = now()`, `save()`.
  2. `cancelByCustomer(): self`
     - Syarat setelah *lock*: `status === 'pending'` **DAN** `payment_status === 'unpaid'`. Jika gagal, lempar `ValidationException::withMessages(['status' => ...])`.
     - Efek: `status = 'cancelled'`, `save()`.
  3. `markCashPaidByAdmin(): self` *(digunakan oleh `RFC-003`)*
     - Syarat setelah *lock*: `payment_method === 'cash'`, `payment_status === 'unpaid'`, dan `status` berada di dalam `['pending', 'preparing', 'ready']`. Jika gagal, lempar `ValidationException::withMessages(['payment_status' => ...])`.
     - Efek: `payment_status = 'paid'`, `paid_at = now()`, `save()`.
  4. `transitionStatusByAdmin(string $targetStatus): self` *(digunakan oleh `RFC-003`)*
     - Syarat setelah *lock*:
       - Ke `'preparing'`: `status === 'pending'` DAN (`payment_method === 'cash'` ATAU `payment_status === 'paid'`).
       - Ke `'ready'`: `status === 'preparing'`.
       - Ke `'completed'`: `status === 'ready'` DAN `payment_status === 'paid'`.
       - Ke `'cancelled'`: `payment_status === 'unpaid'` DAN `status` berada di dalam `['pending', 'preparing', 'ready']`.
       - Seluruh target atau kondisi lain ditolak dengan `ValidationException::withMessages(['status' => ...])`.
     - Efek: `status = $targetStatus`, `save()`.

### 2.4 Authorization (`EnsureUserIsAdmin` & `OrderPolicy`) (`F5`, `SEC-1`)
- **`App\Http\Middleware\EnsureUserIsAdmin`**:
  - Didaftarkan dengan alias `'admin'` pada `bootstrap/app.php`.
  - Jika `!$request->user()`, kembalikan `redirect()->guest(route('login'))`.
  - Jika `!$request->user()->isAdmin()`, panggil `abort(403)`.
  - `RFC-002` mendaftarkan rute `GET /admin` (`admin.dashboard`, middleware `['web', 'auth', 'admin']`) sebagai entrypoint admin yang memverifikasi proteksi middleware ini (dan pada `RFC-003` diarahkan ke daftar pesanan `/admin/orders`).
- **`App\Policies\OrderPolicy`**:
  - `view(User $user, Order $order): bool` -> `$user->id === $order->user_id`
  - `pay(User $user, Order $order): bool` -> `$user->id === $order->user_id`
  - `cancel(User $user, Order $order): bool` -> `$user->id === $order->user_id`
  - Jika pelanggan mengakses halaman detail, endpoint bayar, atau endpoint batal milik pesanan pengguna lain, Laravel mengembalikan `403 Forbidden`.

---

## 3. Routes & Interfaces Exposed

| Method | URI | Route Name | Middleware | Keterangan |
|---|---|---|---|---|
| `GET` | `/cart` | `cart.index` | `web` | Menampilkan isi keranjang session & total sementara |
| `POST` | `/cart/items` | `cart.store` | `web` | Menambah menu ke keranjang session |
| `PATCH` | `/cart/items/{menuId}` | `cart.update` | `web` | Mengubah kuantitas item di keranjang (`1..99`, `{menuId}` raw integer) |
| `DELETE` | `/cart/items/{menuId}` | `cart.destroy` | `web` | Menghapus item dari keranjang (`{menuId}` raw integer) |
| `GET` | `/checkout` | `checkout.index` | `web`, `auth` | Halaman form checkout takeaway (redirect ke `/menus` jika cart kosong) |
| `POST` | `/checkout` | `checkout.store` | `web`, `auth` | Memproses transaksi checkout & membuat pesanan |
| `GET` | `/orders` | `orders.index` | `web`, `auth` | Daftar riwayat pesanan milik pelanggan yang sedang login |
| `GET` | `/orders/{order}` | `orders.show` | `web`, `auth`, `can:view,order` | Detail pesanan pelanggan, QRIS simulasi, tombol bayar/batal |
| `POST` | `/orders/{order}/pay` | `orders.pay` | `web`, `auth`, `can:pay,order` | Aksi tombol **"Saya sudah bayar"** untuk `qris_dummy` |
| `POST` | `/orders/{order}/cancel` | `orders.cancel` | `web`, `auth`, `can:cancel,order` | Aksi batalkan pesanan oleh pelanggan (`pending` & `unpaid`) |
| `GET` | `/admin` | `admin.dashboard` | `web`, `auth`, `admin` | Entrypoint admin yang dilindungi middleware `EnsureUserIsAdmin` |

---

## 4. Implementer's Choices (Intentionally Flexible)

- Bentuk visual gambar/SVG QRIS dummy pada halaman detail pesanan, selama memuat teks label **"SIMULASI"** secara jelas dan bukan merupakan kode QRIS pembayaran nyata (`UI-2`).
- Target redirect setelah `POST /cart/items`, `PATCH /cart/items/{menuId}`, `DELETE /cart/items/{menuId}`, `POST /orders/{order}/pay`, dan `POST /orders/{order}/cancel` (misalnya kembali ke halaman sebelumnya `back()`, ke `cart.index`, atau ke `orders.show`).
- Penempatan indikator jumlah item keranjang pada navbar dan tata letak kartu ringkasan pesanan di halaman `/cart`, `/checkout`, `/orders`, dan `/orders/{order}`, selama mengikuti palet warna dan aturan responsivitas `UI-1`.

---

## 5. Acceptance Criteria

1. **AC-1 (Operasi Keranjang Session & Validasi Kuantitas)**: Pengunjung tamu maupun pelanggan yang login dapat menambahkan menu dengan `is_available = true` ke `session('cart')` (`POST /cart/items`), mengubah kuantitas dalam rentang `1` s/d `99` (`PATCH /cart/items/{menuId}`), dan menghapus item (`DELETE /cart/items/{menuId}`); upaya menambahkan menu dengan `is_available = false` atau kuantitas di luar `1..99` ditolak dengan error validasi.
2. **AC-2 (Ketahanan Keranjang Tamu Saat Login Menuju Checkout)**: Ketika tamu yang sudah memiliki item di `session('cart')` mengakses `GET /checkout`, sistem mengarahkan ke `/login`; setelah tamu berhasil login, seluruh item di `session('cart')` tetap utuh dan pengguna diarahkan ke `/checkout`.
3. **AC-3 (Tampilan Checkout Takeaway & Validasi Catatan)**: Halaman `GET /checkout` menampilkan informasi pemesanan **Takeaway / Pick-up di kasir**, teks estimasi waktu siap dari `config('gumi.estimated_pickup_text')`, pilihan metode pembayaran (`cash` atau `qris_dummy`), serta input `notes` yang menolak teks lebih dari `500` karakter saat `POST /checkout`.
4. **AC-4 (Penolakan Checkout di Luar Jam Operasional)**: Permintaan `POST /checkout` pada waktu (`Asia/Jakarta`) di luar rentang `config('gumi.operating_hours.open')` hingga `config('gumi.operating_hours.close')` ditolak dengan error validasi (`checkout`) dan tidak membuat baris baru di tabel `orders`.
5. **AC-5 (Pengecekan Ulang Ketersediaan Menu Saat Checkout)**: Jika salah satu menu di dalam keranjang session diubah menjadi `is_available = false` (atau dihapus dari database) sebelum `POST /checkout` dikirim, transaksi checkout dibatalkan, tidak ada pesanan yang dibuat, dan pesan error validasi (`checkout`) menyebutkan nama menu yang tidak tersedia tersebut.
6. **AC-6 (Kalkulasi Harga Server, Pembuatan `order_code` & Snapshot Item)**: Saat `POST /checkout` berhasil, `unit_price`, `subtotal`, dan `total` dihitung murni dari `menus.price` di database (mengabaikan parameter harga yang disuntikkan dari request), baris `orders` tersimpan dengan `status = 'pending'`, `payment_status = 'unpaid'`, dan `order_code` terisi format `'GM-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)`, baris `order_items` menyimpan salinan `menu_name`, `unit_price`, `quantity`, dan `subtotal`, serta `session('cart')` dikosongkan.
7. **AC-7 (Simulasi Pembayaran `qris_dummy` oleh Pemilik Pesanan)**: Pada halaman `GET /orders/{order}` untuk pesanan `payment_method = qris_dummy` yang masih `pending` dan `unpaid`, ditampilkan gambar QRIS berlabel **"SIMULASI"** dan tombol **"Saya sudah bayar"**; pengiriman `POST /orders/{order}/pay` oleh pemilik pesanan mengubah `payment_status` menjadi `'paid'` dan mengisi `paid_at`, sedangkan permintaan yang sama ditolak jika `payment_method = 'cash'`, `status !== 'pending'`, atau `payment_status === 'paid'`.
8. **AC-8 (Aturan Pembatalan Pesanan oleh Pelanggan)**: Pengiriman `POST /orders/{order}/cancel` oleh pemilik pesanan mengubah `status` menjadi `'cancelled'` hanya ketika `status === 'pending'` **DAN** `payment_status === 'unpaid'`; permintaan pembatalan oleh pelanggan ditolak apabila pesanan sudah berstatus `payment_status === 'paid'` atau `status` sudah berada pada `'preparing'`, `'ready'`, `'completed'`, atau `'cancelled'`.
9. **AC-9 (Isolasi Pesanan Antar-Pelanggan via `OrderPolicy`)**: Pelanggan yang mencoba mengakses `GET /orders/{order}`, `POST /orders/{order}/pay`, atau `POST /orders/{order}/cancel` milik pelanggan lain menerima respons `403 Forbidden`, dan halaman `GET /orders` hanya menampilkan daftar pesanan milik pelanggan yang sedang login.
10. **AC-10 (Middleware `EnsureUserIsAdmin` pada Rute `/admin`)**: Pengguna ber-role `customer` yang mengakses `GET /admin` (atau rute ber-middleware `admin`) menerima respons `403 Forbidden`, pengunjung yang belum login diarahkan ke halaman `/login`, dan pengguna ber-role `admin` diizinkan mengaksesnya.
