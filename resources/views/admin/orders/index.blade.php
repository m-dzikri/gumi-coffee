@php
    $statusLabels = [
        'pending' => 'Pending',
        'preparing' => 'Preparing',
        'ready' => 'Ready',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    $actionButtonLabels = [
        'preparing' => 'Proses (Preparing)',
        'ready' => 'Siap Diambil (Ready)',
        'completed' => 'Selesaikan (Completed)',
        'cancelled' => 'Batalkan Pesanan',
    ];
@endphp
<x-app-layout>
    <x-slot name="title">Manajemen Pesanan Admin - Gumi Coffee Club</x-slot>

    <section class="w-full bg-[#e9bfd2] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8 border-b-2 border-[#020202]/15">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#020202]">
                    GUMI COFFEE CLUB • ADMIN OPERATIONS
                </p>
                <h1 class="mt-2 text-4xl sm:text-5xl font-black uppercase tracking-tight leading-[0.95] text-[#b22e3b]">
                    MANAJEMEN <span class="italic text-[#020202]">PESANAN.</span>
                </h1>
                <p class="mt-3 text-sm sm:text-base font-medium text-[#020202] max-w-2xl">
                    Pantau pesanan takeaway masuk, cari kode pesanan pelanggan, tandai pembayaran tunai di kasir, dan perbarui status pesanan secara berurutan.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.orders.index') }}" class="pop-btn-crimson px-5 py-2.5 text-xs">
                    Pesanan Masuk
                </a>
                <a href="{{ route('admin.menus.index') }}" class="pop-btn-outline-dark bg-[#fffaee] px-5 py-2.5 text-xs">
                    Kelola Katalog Menu
                </a>
            </div>
        </div>
    </section>

    <section class="w-full bg-[#fffaee] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-8">
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

            <div class="bg-[#e9bfd2]/45 border-2 border-[#020202] p-5 sm:p-6 space-y-5">
                <form x-data="{ submitting: false }"
                      @submit="submitting = true"
                      method="GET"
                      action="{{ route('admin.orders.index') }}"
                      class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                    @if($activeStatus)
                        <input type="hidden" name="status" value="{{ $activeStatus }}" />
                    @endif

                    <div class="flex-1">
                        <label for="admin-order-search" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-1.5">
                            Cari Kode Pesanan (order_code)
                        </label>
                        <input id="admin-order-search"
                               name="search"
                               type="search"
                               value="{{ $search }}"
                               placeholder="Contoh: GM-0042 atau 0042"
                               class="pop-input" />
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="submit"
                                :disabled="submitting"
                                :aria-busy="submitting.toString()"
                                class="pop-btn-crimson px-6 py-2.5 text-xs">
                            <span x-show="!submitting">Cari Kode</span>
                            <span x-show="submitting" x-cloak>Mencari...</span>
                        </button>
                        @if($activeStatus || $search !== '')
                            <a href="{{ route('admin.orders.index') }}" class="pop-btn-outline-dark bg-[#fffaee] px-5 py-2.5 text-xs">
                                Reset Filter
                            </a>
                        @endif
                    </div>
                </form>

                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-[#020202] mb-2">
                        Filter Status Pesanan
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.orders.index', array_filter(['search' => $search !== '' ? $search : null])) }}"
                           class="min-h-[44px] inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-wider border-2 border-[#020202] transition-colors {{ $activeStatus === null ? 'bg-[#020202] text-[#fffaee]' : 'bg-[#fffaee] text-[#020202] hover:bg-[#e9bfd2]' }}">
                            Semua Status
                        </a>
                        @foreach($validStatuses as $statusKey)
                            <a href="{{ route('admin.orders.index', array_filter(['status' => $statusKey, 'search' => $search !== '' ? $search : null])) }}"
                               class="min-h-[44px] inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-wider border-2 border-[#020202] transition-colors {{ $activeStatus === $statusKey ? 'bg-[#b22e3b] text-[#fffaee]' : 'bg-[#fffaee] text-[#020202] hover:bg-[#e9bfd2]' }}">
                                {{ $statusLabels[$statusKey] ?? strtoupper($statusKey) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                @forelse($orders as $order)
                    <article class="bg-[#fffaee] border-2 border-[#020202] p-5 sm:p-6 space-y-4">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b-2 border-[#020202]/15 pb-4">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <span class="text-2xl font-black text-[#020202]">
                                        {{ $order->order_code }}
                                    </span>
                                    <span class="px-3 py-1 bg-[#b22e3b] text-[#fffaee] text-xs font-black uppercase tracking-wider">
                                        STATUS: {{ strtoupper($order->status) }}
                                    </span>
                                    <span class="px-3 py-1 bg-[#020202] text-[#fffaee] text-xs font-black uppercase tracking-wider">
                                        {{ strtoupper($order->payment_method) }} • {{ strtoupper($order->payment_status) }}
                                    </span>
                                </div>
                                <p class="text-xs font-bold text-[#020202]/80">
                                    Pelanggan: <strong>{{ $order->user?->name ?? 'Pelanggan' }}</strong> ({{ $order->user?->email ?? '-' }}) •
                                    {{ $order->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <span class="text-xl font-black text-[#b22e3b]">
                                    Rp {{ number_format($order->total, 0, ',', '.') }}
                                </span>
                                <a href="{{ route('admin.orders.show', $order) }}" class="pop-btn-outline-dark px-4 py-2 text-xs">
                                    Detail &amp; Kontrol
                                </a>
                            </div>
                        </div>

                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="space-y-1 text-sm">
                                <p class="font-bold text-[#020202]">
                                    @foreach($order->items as $item)
                                        <span>{{ $item->quantity }}x {{ $item->menu_name }}</span>@if(! $loop->last), @endif
                                    @endforeach
                                </p>
                                @if($order->notes)
                                    <p class="text-xs font-bold text-[#b22e3b]">
                                        Catatan: "{{ $order->notes }}"
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2.5">
                                @if($order->canMarkCashPaidByAdmin())
                                    <form x-data="{ submitting: false }"
                                          @submit="submitting = true"
                                          method="POST"
                                          action="{{ route('admin.orders.mark-paid', $order) }}">
                                        @csrf
                                        <button type="submit"
                                                :disabled="submitting"
                                                :aria-busy="submitting.toString()"
                                                class="pop-btn-crimson px-4 py-2 text-xs">
                                            <span x-show="!submitting">Tandai Lunas (Cash)</span>
                                            <span x-show="submitting" x-cloak>Memproses...</span>
                                        </button>
                                    </form>
                                @endif

                                @foreach($order->allowedAdminTargetStatuses() as $targetStatus)
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
                                                class="{{ $targetStatus === 'cancelled' ? 'min-h-[44px] inline-flex items-center justify-center px-4 py-2 bg-[#020202] text-[#fffaee] text-xs font-black uppercase tracking-wider border-2 border-[#020202] hover:bg-[#b22e3b] hover:border-[#b22e3b] transition-colors' : 'pop-btn-outline-dark bg-[#e9bfd2] px-4 py-2 text-xs' }}">
                                            <span x-show="!submitting">{{ $actionButtonLabels[$targetStatus] ?? strtoupper($targetStatus) }}</span>
                                            <span x-show="submitting" x-cloak>Memproses...</span>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="p-8 bg-[#e9bfd2] border-2 border-[#020202] text-center space-y-2">
                        <h2 class="text-xl font-black uppercase text-[#020202]">
                            Tidak Ada Pesanan Ditemukan
                        </h2>
                        <p class="text-sm font-medium text-[#020202]">
                            Belum ada pesanan yang cocok dengan filter status atau pencarian kode pesanan saat ini.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-app-layout>
