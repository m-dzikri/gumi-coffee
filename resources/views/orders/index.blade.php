<x-app-layout>
    <x-slot name="title">Riwayat Pesanan Saya - Gumi Coffee Club</x-slot>

    <section class="w-full bg-[#e9bfd2] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8 border-b-2 border-[#020202]/15">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#020202]">
                    GUMI COFFEE CLUB • CUSTOMER ORDERS
                </p>
                <h1 class="mt-2 text-4xl sm:text-5xl font-black uppercase tracking-tight leading-[0.95] text-[#b22e3b]">
                    RIWAYAT <span class="italic text-[#020202]">PESANAN.</span>
                </h1>
                <p class="mt-3 text-sm sm:text-base font-medium text-[#020202] max-w-2xl">
                    Pantau status pembuatan minuman takeaway Anda dan tunjukkan kode pesanan di kasir {{ $location }}.
                </p>
            </div>

            <a href="{{ route('menus.index') }}" class="pop-btn-crimson">
                + Pesan Menu Baru
            </a>
        </div>
    </section>

    <section class="w-full bg-[#fffaee] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">
            @forelse($orders as $order)
                <article class="bg-[#fffaee] border-2 border-[#020202] p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="px-3 py-1 bg-[#b22e3b] text-[#fffaee] text-sm font-black uppercase tracking-wider">
                                {{ $order->order_code }}
                            </span>
                            <span class="px-3 py-1 bg-[#e9bfd2] text-[#020202] border border-[#020202] text-xs font-black uppercase tracking-wider">
                                Status: {{ strtoupper($order->status) }}
                            </span>
                            <span class="px-3 py-1 {{ $order->payment_status === 'paid' ? 'bg-[#020202] text-[#fffaee]' : 'bg-[#fffaee] text-[#020202] border border-[#020202]' }} text-xs font-black uppercase tracking-wider">
                                {{ strtoupper($order->payment_status) }} ({{ $order->payment_method === 'qris_dummy' ? 'QRIS SIMULASI' : 'CASH' }})
                            </span>
                        </div>

                        <p class="text-xs font-bold text-[#020202]/75">
                            Dibuat pada {{ $order->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB • {{ $estimatedPickupText }}
                        </p>

                        <p class="text-sm font-bold text-[#020202]">
                            {{ $order->items->map(fn ($item) => $item->quantity . 'x ' . $item->menu_name)->implode(', ') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center justify-between md:justify-end gap-5 pt-4 md:pt-0 border-t md:border-t-0 border-[#020202]/15">
                        <div class="md:text-right">
                            <span class="block text-[10px] font-black uppercase tracking-wider text-[#020202]/70">
                                Total Pesanan
                            </span>
                            <span class="text-xl font-black text-[#b22e3b]">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </span>
                        </div>

                        <a href="{{ route('orders.show', $order) }}" class="pop-btn-outline-dark">
                            Lihat Detail
                        </a>
                    </div>
                </article>
            @empty
                <div class="bg-[#e9bfd2] border-2 border-[#020202] p-10 text-center space-y-4">
                    <h2 class="text-2xl font-black uppercase tracking-tight text-[#020202]">
                        Belum Ada Riwayat Pesanan
                    </h2>
                    <p class="text-sm font-medium text-[#020202] max-w-md mx-auto">
                        Anda belum memiliki pesanan takeaway. Silakan pilih menu favorit Anda di katalog Gumi Coffee.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('menus.index') }}" class="pop-btn-crimson">
                            Jelajahi Katalog Menu
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
</x-app-layout>
