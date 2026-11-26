<x-app-layout>
    <x-slot name="title">Katalog Menu - Gumi Coffee Club</x-slot>

    {{-- Catalog Editorial Header Band (#e9bfd2) --}}
    <section class="w-full bg-[#e9bfd2] text-[#020202] py-12 sm:py-16 px-4 sm:px-6 lg:px-8 border-b-2 border-[#020202]/15">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#b22e3b]">
                    GUMI COFFEE CLUB • TAKEAWAY CATALOG
                </p>
                <h1 class="mt-2 text-4xl sm:text-5xl lg:text-6xl font-black uppercase tracking-tight leading-[0.95] text-[#b22e3b]">
                    KATALOG <span class="italic text-[#020202]">MENU.</span>
                </h1>
                <p class="mt-3 text-sm sm:text-base font-medium text-[#020202] max-w-2xl">
                    Seluruh minuman dan pastry disiapkan segar untuk layanan takeaway / pick-up di kasir ({{ $location }}). Jam operasional pemesanan: {{ $operatingHours['open'] }} - {{ $operatingHours['close'] }} WIB.
                </p>
            </div>

            {{-- Category Quick Filter Anchors --}}
            <div class="flex flex-wrap gap-2.5">
                @foreach($categories as $category)
                    <a href="#kategori-{{ \Illuminate\Support\Str::slug($category) }}"
                       class="pop-btn-outline-dark bg-[#fffaee]">
                        {{ strtoupper($category) }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Menu Listing Grouped by Category (#fffaee) --}}
    <section class="w-full bg-[#fffaee] text-[#020202] py-12 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-12">
            @if(session('status'))
                <div role="status" class="p-4 bg-[#e9bfd2] border-2 border-[#020202] flex flex-wrap items-center justify-between gap-4">
                    <span class="text-sm font-black uppercase tracking-wider text-[#020202]">
                        {{ session('status') }}
                    </span>
                    <a href="{{ route('cart.index') }}" class="pop-btn-crimson py-2 px-5 text-xs">
                        Lihat Keranjang
                    </a>
                </div>
            @endif

            @if($errors->any())
                <div role="alert" class="p-4 bg-[#b22e3b] text-[#fffaee] border-2 border-[#020202] space-y-1">
                    @foreach($errors->all() as $error)
                        <p class="text-sm font-bold">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="space-y-16">
            @forelse($menusByCategory as $category => $menus)
                <div id="kategori-{{ \Illuminate\Support\Str::slug($category) }}" class="scroll-mt-24">
                    <div class="flex flex-wrap items-baseline justify-between gap-4 pb-4 border-b-2 border-[#020202]">
                        <h2 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-[#b22e3b]">
                            {{ $category }}
                        </h2>
                        <span class="text-xs font-black uppercase tracking-[0.18em] text-[#020202]">
                            {{ $menus->count() }} MENU
                        </span>
                    </div>

                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($menus as $menu)
                            <article class="flex flex-col bg-[#fffaee] border-2 border-[#020202]/15 p-5 transition-colors hover:border-[#b22e3b]">
                                {{-- Product Visual or Editorial Fallback Placeholder --}}
                                <div class="relative w-full aspect-[4/3] bg-[#fffaee] flex items-center justify-center overflow-hidden">
                                    @if($menu->image_path)
                                        <img src="{{ asset($menu->image_path) }}"
                                             alt="{{ $menu->name }}"
                                             width="400"
                                             height="320"
                                             loading="lazy"
                                             class="w-full h-full object-contain {{ $menu->is_available ? '' : 'opacity-60 grayscale' }}" />
                                    @else
                                        <div data-testid="menu-image-placeholder"
                                             class="w-full h-full bg-[#e9bfd2] flex flex-col items-center justify-center p-6 text-center">
                                            <svg class="w-12 h-12 text-[#b22e3b]" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M16 44 A16 16 0 0 1 48 44 Z" fill="currentColor"/>
                                                <line x1="32" y1="20" x2="32" y2="10" stroke="currentColor" stroke-width="4"/>
                                                <line x1="18" y1="26" x2="10" y2="18" stroke="currentColor" stroke-width="4"/>
                                                <line x1="46" y1="26" x2="54" y2="18" stroke="currentColor" stroke-width="4"/>
                                            </svg>
                                            <span class="mt-2 text-xs font-black uppercase tracking-wider text-[#020202]">
                                                Gambar Belum Tersedia
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Availability Status Badge --}}
                                    <div class="absolute top-2 right-2">
                                        @if($menu->is_available)
                                            <span class="inline-block px-3 py-1 bg-[#b22e3b] text-[#fffaee] text-[11px] font-black uppercase tracking-wider">
                                                Tersedia
                                            </span>
                                        @else
                                            <span class="inline-block px-3 py-1 bg-[#020202] text-[#fffaee] text-[11px] font-black uppercase tracking-wider">
                                                Habis / Tidak Tersedia
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Menu Copy & Rupiah Price --}}
                                <div class="mt-5 flex-1 flex flex-col justify-between">
                                    <div>
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#b22e3b]">
                                            {{ $menu->category }}
                                        </p>
                                        <h3 class="mt-1 text-xl font-black uppercase tracking-tight text-[#020202]">
                                            {{ $menu->name }}
                                        </h3>
                                        @if($menu->description)
                                            <p class="mt-2 text-sm text-[#020202]/80 leading-relaxed">
                                                {{ $menu->description }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mt-5 pt-4 border-t border-[#020202]/15 space-y-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-lg font-black text-[#020202]">
                                                Rp {{ number_format($menu->price, 0, ',', '.') }}
                                            </span>
                                            <span class="text-xs font-black uppercase tracking-wider {{ $menu->is_available ? 'text-[#b22e3b]' : 'text-[#020202]/60' }}">
                                                {{ $menu->is_available ? 'Siap Dipesan' : 'Stok Habis' }}
                                            </span>
                                        </div>

                                        @if($menu->is_available)
                                            <form method="POST" action="{{ route('cart.store') }}" class="flex items-center gap-2">
                                                @csrf
                                                <input type="hidden" name="menu_id" value="{{ $menu->id }}" />
                                                <label for="qty-menu-{{ $menu->id }}" class="sr-only">
                                                    Kuantitas {{ $menu->name }}
                                                </label>
                                                <input id="qty-menu-{{ $menu->id }}"
                                                       name="quantity"
                                                       type="number"
                                                       min="1"
                                                       max="99"
                                                       value="1"
                                                       required
                                                       class="w-20 min-h-[44px] px-3 py-2 bg-[#fffaee] text-[#020202] border-2 border-[#020202] font-black text-sm text-center focus:outline-none focus:ring-2 focus:ring-[#b22e3b]" />
                                                <button type="submit" class="pop-btn-crimson flex-1 px-4 py-2.5 text-xs">
                                                    + Keranjang
                                                </button>
                                            </form>
                                        @else
                                            <button type="button"
                                                    disabled
                                                    class="w-full min-h-[44px] px-4 py-2.5 bg-[#020202]/15 text-[#020202]/60 border-2 border-[#020202]/20 font-black uppercase tracking-wider text-xs">
                                                Tidak Tersedia
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="py-12 px-6 bg-[#e9bfd2] text-center">
                    <h2 class="text-xl font-black uppercase text-[#020202]">
                        Belum Ada Menu Tersedia
                    </h2>
                    <p class="mt-2 text-sm font-medium text-[#020202]">
                        Katalog menu Gumi Coffee sedang diperbarui. Silakan kembali beberapa saat lagi.
                    </p>
                </div>
            @endforelse
            </div>
        </div>
    </section>
</x-app-layout>
