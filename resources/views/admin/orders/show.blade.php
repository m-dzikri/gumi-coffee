@php
    $actionButtonLabels = [
        'preparing' => 'Proses Pesanan (Preparing)',
        'ready' => 'Tandai Siap Diambil (Ready)',
        'completed' => 'Selesaikan Pesanan (Completed)',
        'cancelled' => 'Batalkan Pesanan (Cancelled)',
    ];
@endphp
<x-app-layout>
    <x-slot name="title">Admin Detail Pesanan {{ $order->order_code }} - Gumi Coffee Club</x-slot>

    <section class="w-full bg-[#e9bfd2] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8 border-b-2 border-[#020202]/15">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#020202]">
                    GUMI COFFEE CLUB • ADMIN ORDER CONTROL
                </p>
                <h1 class="mt-2 text-4xl sm:text-5xl font-black uppercase tracking-tight leading-[0.95] text-[#b22e3b]">
                    KONTROL <span class="italic text-[#020202]">{{ $order->order_code }}</span>
                </h1>
                <p class="mt-3 text-sm sm:text-base font-medium text-[#020202] max-w-2xl">
                    Kelola pembayaran tunai dan transisi status pesanan pelanggan melalui state machine terpusat.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.orders.index') }}" class="pop-btn-outline-dark bg-[#fffaee] px-5 py-2.5 text-xs">
                    Daftar Pesanan Admin
                </a>
                <a href="{{ route('admin.menus.index') }}" class="pop-btn-outline-dark bg-[#fffaee] px-5 py-2.5 text-xs">
                    Kelola Menu
                </a>
            </div>
        </div>
    </section>

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
                <div class="lg:col-span-7 space-y-6">
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
                                    Pelanggan
                                </dt>
                                <dd class="mt-1 font-black text-[#020202]">
                                    {{ $order->user?->name ?? 'Pelanggan' }}
                                    <span class="block text-xs font-bold text-[#020202]/75">
                                        {{ $order->user?->email ?? '-' }}
                                    </span>
                                </dd>
                            </div>

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
                                            Lunas: {{ $order->paid_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                        </span>
                                    @endif
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
                                <p class="text-xs font-black uppercase tracking-wider text-[#020202]">
                                    Catatan Pesanan (Notes)
                                </p>
                                <p class="mt-1 text-sm font-bold text-[#020202]">
                                    {{ $order->notes }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="bg-[#fffaee] border-2 border-[#020202] p-6 space-y-4">
                        <h2 class="text-xl font-black uppercase tracking-tight text-[#b22e3b]">
                            Rincian Item Pesanan (Snapshot)
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

                <aside class="lg:col-span-5 space-y-6">
                    @if($order->canMarkCashPaidByAdmin())
                        <div class="bg-[#e9bfd2] border-2 border-[#020202] p-6 space-y-4">
                            <div>
                                <span class="inline-block px-3 py-1 bg-[#020202] text-[#fffaee] text-xs font-black uppercase tracking-wider">
                                    PEMBAYARAN TUNAI (CASH)
                                </span>
                                <h2 class="mt-2 text-xl font-black uppercase tracking-tight text-[#020202]">
                                    TANDAI LUNAS DI KASIR
                                </h2>
                                <p class="mt-1 text-xs font-medium text-[#020202]/85">
                                    Gunakan tombol ini setelah pelanggan membayar tunai sebesar <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong> di kasir.
                                </p>
                            </div>

                            <form x-data="{ submitting: false }"
                                  @submit="submitting = true"
                                  method="POST"
                                  action="{{ route('admin.orders.mark-paid', $order) }}">
                                @csrf
                                <button type="submit"
                                        :disabled="submitting"
                                        :aria-busy="submitting.toString()"
                                        class="pop-btn-crimson w-full text-center">
                                    <span x-show="!submitting">Tandai Lunas (Cash)</span>
                                    <span x-show="submitting" x-cloak>Memproses...</span>
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="bg-[#fffaee] border-2 border-[#020202] p-6 space-y-4">
                        <div>
                            <h2 class="text-xl font-black uppercase tracking-tight text-[#020202]">
                                Transisi Status Pesanan
                            </h2>
                            <p class="mt-1 text-xs font-medium text-[#020202]/80">
                                Alur berurutan: <strong>pending -> preparing -> ready -> completed</strong>. Pesanan yang sudah <strong>paid</strong> tidak dapat dibatalkan.
                            </p>
                        </div>

                        @php
                            $allowedTargets = $order->allowedAdminTargetStatuses();
                        @endphp

                        @if(count($allowedTargets) > 0)
                            <div class="space-y-3">
                                @foreach($allowedTargets as $targetStatus)
                                    <form x-data="{ submitting: false }"
                                          @submit="submitting = true"
                                          method="POST"
                                          action="{{ route('admin.orders.update-status', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $targetStatus }}" />
                                        <button type="submit"
                                                :disabled="submitting"
                                                :aria-busy="submitting.toString()"
                                                class="w-full {{ $targetStatus === 'cancelled' ? 'min-h-[44px] px-6 py-3 bg-[#020202] text-[#fffaee] text-xs font-black uppercase tracking-wider hover:bg-[#b22e3b] transition-colors' : 'pop-btn-crimson' }}">
                                            <span x-show="!submitting">{{ $actionButtonLabels[$targetStatus] ?? strtoupper($targetStatus) }}</span>
                                            <span x-show="submitting" x-cloak>Memproses...</span>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 bg-[#e9bfd2]/50 border border-[#020202]/30 text-xs font-bold text-[#020202]">
                                Tidak ada transisi status lanjutan yang tersedia untuk kondisi pesanan saat ini (Status: <strong>{{ strtoupper($order->status) }}</strong>, Pembayaran: <strong>{{ strtoupper($order->payment_status) }}</strong>).
                            </div>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </section>
</x-app-layout>
