<x-app-layout>
    <x-slot name="title">Keranjang Belanja - Gumi Coffee Club</x-slot>

    {{-- Cart Header Band (#e9bfd2) --}}
    <section class="w-full bg-[#e9bfd2] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8 border-b-2 border-[#020202]/15">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#b22e3b]">
                    GUMI COFFEE CLUB • TAKEAWAY CART
                </p>
                <h1 class="mt-2 text-4xl sm:text-5xl font-black uppercase tracking-tight leading-[0.95] text-[#b22e3b]">
                    KERANJANG <span class="italic text-[#020202]">BELANJA.</span>
                </h1>
                <p class="mt-3 text-sm sm:text-base font-medium text-[#020202] max-w-2xl">
                    Periksa kembali pilihan menu takeaway Anda sebelum melanjutkan ke proses checkout ({{ $estimatedPickupText }}).
                </p>
            </div>

            <a href="{{ route('menus.index') }}" class="pop-btn-outline-dark bg-[#fffaee]">
                + Tambah Menu Lain
            </a>
        </div>
    </section>

    {{-- Cart Content (#fffaee) --}}
    <section class="w-full bg-[#fffaee] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">
            @if(session('status'))
                <div role="status" class="p-4 bg-[#e9bfd2] border-2 border-[#020202] text-sm font-black uppercase tracking-wider text-[#020202]">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div role="alert" class="p-4 bg-[#b22e3b] text-[#fffaee] border-2 border-[#020202] space-y-1">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-[#e9bfd2]">PERHATIAN</p>
                    @foreach($errors->all() as $error)
                        <p class="text-sm font-bold">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if(count($cartItems) > 0)
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    {{-- Left Column: Cart Items List --}}
                    <div class="lg:col-span-8 space-y-4">
                        @foreach($cartItems as $item)
                            <article class="bg-[#fffaee] border-2 border-[#020202] p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#b22e3b]">
                                            {{ $item['category'] }}
                                        </span>
                                        @if(!$item['is_available'])
                                            <span class="px-2 py-0.5 bg-[#020202] text-[#fffaee] text-[10px] font-black uppercase tracking-wider">
                                                Tidak Tersedia
                                            </span>
                                        @endif
                                    </div>
                                    <h2 class="text-xl font-black uppercase tracking-tight text-[#020202]">
                                        {{ $item['menu_name'] }}
                                    </h2>
                                    @if($item['exists'])
                                        <p class="text-sm font-bold text-[#020202]/80">
                                            Harga Satuan: Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                        </p>
                                    @else
                                        <p class="text-xs font-bold text-[#b22e3b]">
                                            Menu ini sudah tidak tersedia di katalog. Silakan hapus dari keranjang.
                                        </p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-4 sm:justify-end">
                                    @if($item['is_available'])
                                        <form method="POST" action="{{ route('cart.update', $item['menu_id']) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <label for="cart-qty-{{ $item['menu_id'] }}" class="text-xs font-black uppercase tracking-wider text-[#020202]">
                                                Qty
                                            </label>
                                            <input id="cart-qty-{{ $item['menu_id'] }}"
                                                   name="quantity"
                                                   type="number"
                                                   min="1"
                                                   max="99"
                                                   value="{{ $item['quantity'] }}"
                                                   required
                                                   class="w-20 min-h-[44px] px-3 py-2 bg-[#fffaee] text-[#020202] border-2 border-[#020202] font-black text-sm text-center focus:outline-none focus:ring-2 focus:ring-[#b22e3b]" />
                                            <button type="submit" class="pop-btn-outline-dark px-4 py-2 text-xs">
                                                Ubah
                                            </button>
                                        </form>
                                    @endif

                                    <div class="min-w-[110px] sm:text-right">
                                        <span class="block text-[10px] font-black uppercase tracking-wider text-[#020202]/70">
                                            Subtotal
                                        </span>
                                        <span class="text-base font-black text-[#020202]">
                                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <form method="POST" action="{{ route('cart.destroy', $item['menu_id']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="min-h-[44px] px-4 py-2 bg-[#020202] text-[#fffaee] text-xs font-black uppercase tracking-wider hover:bg-[#b22e3b] transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- Right Column: Order Summary Card --}}
                    <aside class="lg:col-span-4 bg-[#e9bfd2] border-2 border-[#020202] p-6 space-y-5">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-[#b22e3b]">
                                RINGKASAN TAKEAWAY
                            </p>
                            <h2 class="mt-1 text-2xl font-black uppercase tracking-tight text-[#020202]">
                                TOTAL SEMENTARA
                            </h2>
                        </div>

                        <dl class="space-y-3 border-y-2 border-[#020202]/20 py-4 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="font-bold text-[#020202]">Tipe Pesanan</dt>
                                <dd class="font-black uppercase text-[#b22e3b]">Takeaway / Pick-up</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="font-bold text-[#020202]">Lokasi Kasir</dt>
                                <dd class="font-bold text-[#020202] text-right">{{ $location }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="font-bold text-[#020202]">Estimasi Siap</dt>
                                <dd class="font-bold text-[#020202] text-right">{{ $estimatedPickupText }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="font-bold text-[#020202]">Jam Operasional</dt>
                                <dd class="font-bold text-[#020202]">{{ $operatingHours['open'] }} - {{ $operatingHours['close'] }} WIB</dd>
                            </div>
                        </dl>

                        <div class="flex items-baseline justify-between">
                            <span class="text-sm font-black uppercase tracking-wider text-[#020202]">
                                Total Pembayaran
                            </span>
                            <span class="text-2xl font-black text-[#b22e3b]">
                                Rp {{ number_format($total, 0, ',', '.') }}
                            </span>
                        </div>

                        <a href="{{ route('checkout.index') }}" class="pop-btn-crimson w-full text-center">
                            Lanjut ke Checkout
                        </a>
                    </aside>
                </div>
            @else
                <div class="bg-[#e9bfd2] border-2 border-[#020202] p-10 text-center space-y-4">
                    <h2 class="text-2xl font-black uppercase tracking-tight text-[#020202]">
                        Keranjang Belanja Anda Masih Kosong
                    </h2>
                    <p class="text-sm font-medium text-[#020202] max-w-md mx-auto">
                        Pilih racikan kopi atau pastry favorit Anda dari katalog menu untuk mulai memesan takeaway.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('menus.index') }}" class="pop-btn-crimson">
                            Lihat Katalog Menu
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
