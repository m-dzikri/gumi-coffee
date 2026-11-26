<x-app-layout>
    <x-slot name="title">Detail Pesanan {{ $order->order_code }} - Gumi Coffee Club</x-slot>

    {{-- Order Detail Header Band (#e9bfd2) --}}
    <section class="w-full bg-[#e9bfd2] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8 border-b-2 border-[#020202]/15">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#b22e3b]">
                    GUMI COFFEE CLUB • TAKEAWAY ORDER DETAIL
                </p>
                <h1 class="mt-2 text-4xl sm:text-5xl font-black uppercase tracking-tight leading-[0.95] text-[#b22e3b]">
                    PESANAN <span class="italic text-[#020202]">{{ $order->order_code }}</span>
                </h1>
                <p class="mt-3 text-sm sm:text-base font-medium text-[#020202] max-w-2xl">
                    Tunjukkan kode <strong>{{ $order->order_code }}</strong> saat mengambil pesanan di kasir ({{ $location }}). {{ $estimatedPickupText }}.
                </p>
            </div>

            <a href="{{ route('orders.index') }}" class="pop-btn-outline-dark bg-[#fffaee]">
                Semua Pesanan Saya
            </a>
        </div>
    </section>

    {{-- Order Detail Body (#fffaee) --}}
    <section class="w-full bg-[#fffaee] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">
            @if(session('status'))
                <div role="status" class="p-4 bg-[#e9bfd2] border-2 border-[#020202] text-sm font-black uppercase tracking-wider text-[#020202]">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div role="alert" class="p-4 bg-[#b22e3b] text-[#fffaee] border-2 border-[#020202] space-y-1">
                    @foreach($errors->all() as $error)
                        <p class="text-sm font-bold">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {{-- Left Column: Order Metadata & Item Snapshot Table --}}
                <div class="lg:col-span-7 space-y-6">
                    {{-- Order Status Summary Card --}}
                    <div class="bg-[#fffaee] border-2 border-[#020202] p-6 space-y-5">
                        <div class="flex flex-wrap items-center justify-between gap-4 border-b-2 border-[#020202]/15 pb-4">
                            <div>
                                <span class="block text-xs font-black uppercase tracking-[0.18em] text-[#b22e3b]">
                                    KODE PESANAN TAKEAWAY
                                </span>
                                <span class="text-3xl font-black text-[#020202]">
                                    {{ $order->order_code }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-3 py-1.5 bg-[#b22e3b] text-[#fffaee] text-xs font-black uppercase tracking-wider">
                                    STATUS: {{ strtoupper($order->status) }}
                                </span>
                                <span class="px-3 py-1.5 bg-[#020202] text-[#fffaee] text-xs font-black uppercase tracking-wider">
                                    BAYAR: {{ strtoupper($order->payment_status) }}
                                </span>
                            </div>
                        </div>

                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="bg-[#e9bfd2]/40 p-3.5 border border-[#020202]/20">
                                <dt class="text-[11px] font-black uppercase tracking-wider text-[#b22e3b]">
                                    Metode Pembayaran
                                </dt>
                                <dd class="mt-1 font-black text-[#020202]">
                                    {{ $order->payment_method === 'qris_dummy' ? 'QRIS Dummy (qris_dummy)' : 'Tunai di Kasir (cash)' }}
                                </dd>
                            </div>

                            <div class="bg-[#e9bfd2]/40 p-3.5 border border-[#020202]/20">
                                <dt class="text-[11px] font-black uppercase tracking-wider text-[#b22e3b]">
                                    Status Pembayaran
                                </dt>
                                <dd class="mt-1 font-black text-[#020202]">
                                    {{ strtoupper($order->payment_status) }}
                                    @if($order->paid_at)
                                        <span class="block text-xs font-bold text-[#020202]/75">
                                            Lunas pada {{ $order->paid_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                        </span>
                                    @endif
                                </dd>
                            </div>

                            <div class="bg-[#e9bfd2]/40 p-3.5 border border-[#020202]/20">
                                <dt class="text-[11px] font-black uppercase tracking-wider text-[#b22e3b]">
                                    Estimasi &amp; Pengambilan
                                </dt>
                                <dd class="mt-1 font-black text-[#020202]">
                                    Takeaway / Pick-up • {{ $estimatedPickupText }}
                                </dd>
                            </div>

                            <div class="bg-[#e9bfd2]/40 p-3.5 border border-[#020202]/20">
                                <dt class="text-[11px] font-black uppercase tracking-wider text-[#b22e3b]">
                                    Waktu Pemesanan
                                </dt>
                                <dd class="mt-1 font-black text-[#020202]">
                                    {{ $order->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                </dd>
                            </div>
                        </dl>

                        @if($order->notes)
                            <div class="p-4 bg-[#e9bfd2] border-2 border-[#020202]">
                                <p class="text-xs font-black uppercase tracking-wider text-[#b22e3b]">
                                    Catatan Pesanan (Notes)
                                </p>
                                <p class="mt-1 text-sm font-bold text-[#020202]">
                                    {{ $order->notes }}
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Snapshot Order Items List --}}
                    <div class="bg-[#fffaee] border-2 border-[#020202] p-6 space-y-4">
                        <h2 class="text-xl font-black uppercase tracking-tight text-[#b22e3b]">
                            Rincian Item Pesanan
                        </h2>

                        <div class="divide-y-2 divide-[#020202]/15 border-y-2 border-[#020202]/20">
                            @foreach($order->items as $item)
                                <div class="py-4 flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-base font-black uppercase text-[#020202]">
                                            {{ $item->menu_name }}
                                        </p>
                                        <p class="text-xs font-bold text-[#020202]/75">
                                            {{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <span class="text-base font-black text-[#020202]">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-baseline justify-between pt-2">
                            <span class="text-sm font-black uppercase tracking-wider text-[#020202]">
                                Total Pembayaran
                            </span>
                            <span class="text-2xl font-black text-[#b22e3b]">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Right Column: QRIS Dummy Simulation & Customer Actions --}}
                <aside class="lg:col-span-5 space-y-6">
                    @if($order->payment_method === 'qris_dummy')
                        <div class="bg-[#e9bfd2] border-2 border-[#020202] p-6 space-y-5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-3 py-1 bg-[#b22e3b] text-[#fffaee] text-xs font-black uppercase tracking-[0.2em]">
                                    SIMULASI
                                </span>
                                <span class="text-xs font-black uppercase tracking-wider text-[#020202]">
                                    QRIS DUMMY • BUKAN KODE NYATA
                                </span>
                            </div>

                            <div>
                                <h2 class="text-2xl font-black uppercase tracking-tight text-[#020202]">
                                    PEMBAYARAN QRIS SIMULASI
                                </h2>
                                <p class="mt-1 text-xs font-bold text-[#020202]/80">
                                    Kode di bawah ini adalah visual <strong>SIMULASI</strong> untuk keperluan demo portofolio Gumi Coffee (tidak terhubung ke jaringan pembayaran nyata).
                                </p>
                            </div>

                            {{-- Simulated QRIS Graphic Box --}}
                            <div class="bg-[#fffaee] border-2 border-[#020202] p-6 flex flex-col items-center justify-center text-center space-y-3">
                                <span class="px-3 py-1 bg-[#020202] text-[#fffaee] text-[11px] font-black uppercase tracking-[0.22em]">
                                    QRIS SIMULASI GUMI COFFEE
                                </span>

                                <svg class="w-48 h-48 text-[#020202]" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Gambar QRIS SIMULASI">
                                    <rect width="160" height="160" fill="#fffaee"/>
                                    {{-- Finder Patterns --}}
                                    <rect x="12" y="12" width="40" height="40" stroke="#020202" stroke-width="6"/>
                                    <rect x="24" y="24" width="16" height="16" fill="#b22e3b"/>
                                    <rect x="108" y="12" width="40" height="40" stroke="#020202" stroke-width="6"/>
                                    <rect x="120" y="24" width="16" height="16" fill="#b22e3b"/>
                                    <rect x="12" y="108" width="40" height="40" stroke="#020202" stroke-width="6"/>
                                    <rect x="24" y="120" width="16" height="16" fill="#b22e3b"/>
                                    {{-- Simulated Data Matrix Blocks --}}
                                    <rect x="64" y="16" width="12" height="12" fill="#020202"/>
                                    <rect x="84" y="16" width="12" height="12" fill="#020202"/>
                                    <rect x="64" y="36" width="24" height="12" fill="#020202"/>
                                    <rect x="16" y="64" width="16" height="12" fill="#020202"/>
                                    <rect x="40" y="64" width="12" height="24" fill="#020202"/>
                                    <rect x="108" y="64" width="16" height="16" fill="#020202"/>
                                    <rect x="132" y="64" width="16" height="24" fill="#020202"/>
                                    <rect x="64" y="108" width="16" height="16" fill="#020202"/>
                                    <rect x="88" y="108" width="20" height="12" fill="#020202"/>
                                    <rect x="116" y="116" width="32" height="12" fill="#020202"/>
                                    <rect x="64" y="132" width="28" height="16" fill="#020202"/>
                                    <rect x="104" y="136" width="44" height="12" fill="#b22e3b"/>
                                    {{-- Center Simulation Banner --}}
                                    <rect x="28" y="66" width="104" height="28" fill="#b22e3b" stroke="#020202" stroke-width="3"/>
                                    <text x="80" y="84" text-anchor="middle" fill="#fffaee" font-size="13" font-weight="900" letter-spacing="2">SIMULASI</text>
                                </svg>

                                <p class="text-xs font-black uppercase tracking-wider text-[#020202]">
                                    {{ $order->order_code }} • Rp {{ number_format($order->total, 0, ',', '.') }}
                                </p>
                            </div>

                            @if($order->status === 'pending' && $order->payment_status === 'unpaid')
                                <form method="POST" action="{{ route('orders.pay', $order) }}">
                                    @csrf
                                    <button type="submit" class="pop-btn-crimson w-full text-center">
                                        Saya sudah bayar
                                    </button>
                                </form>
                            @elseif($order->payment_status === 'paid')
                                <div class="p-3.5 bg-[#020202] text-[#fffaee] text-center text-xs font-black uppercase tracking-wider">
                                    Pembayaran Simulasi QRIS Telah Lunas (PAID)
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-[#e9bfd2] border-2 border-[#020202] p-6 space-y-3">
                            <span class="inline-block px-3 py-1 bg-[#020202] text-[#fffaee] text-xs font-black uppercase tracking-wider">
                                PEMBAYARAN TUNAI (CASH)
                            </span>
                            <h2 class="text-xl font-black uppercase tracking-tight text-[#020202]">
                                BAYAR DI KASIR SAAT PICK-UP
                            </h2>
                            <p class="text-sm font-medium text-[#020202]">
                                Silakan lakukan pembayaran tunai sebesar <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong> kepada kasir di {{ $location }} dengan menyebutkan kode <strong>{{ $order->order_code }}</strong>.
                            </p>
                        </div>
                    @endif

                    {{-- Customer Cancellation Box (Only when pending & unpaid) --}}
                    @if($order->status === 'pending' && $order->payment_status === 'unpaid')
                        <div class="bg-[#fffaee] border-2 border-[#020202] p-6 space-y-4">
                            <div>
                                <h2 class="text-lg font-black uppercase tracking-tight text-[#020202]">
                                    Ingin Membatalkan Pesanan?
                                </h2>
                                <p class="mt-1 text-xs font-medium text-[#020202]/80">
                                    Pesanan hanya dapat dibatalkan selama masih berstatus <strong>pending</strong> dan <strong>unpaid</strong>.
                                </p>
                            </div>

                            <form method="POST" action="{{ route('orders.cancel', $order) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full min-h-[44px] px-6 py-3 bg-[#020202] text-[#fffaee] text-xs font-black uppercase tracking-wider hover:bg-[#b22e3b] transition-colors">
                                    Batalkan Pesanan
                                </button>
                            </form>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </section>
</x-app-layout>
