<x-app-layout>
    <x-slot name="title">Manajemen Katalog Menu Admin - Gumi Coffee Club</x-slot>

    {{-- Admin Menus Header Band (#e9bfd2) --}}
    <section class="w-full bg-[#e9bfd2] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8 border-b-2 border-[#020202]/15">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#b22e3b]">
                    GUMI COFFEE CLUB • ADMIN CATALOG MANAGEMENT
                </p>
                <h1 class="mt-2 text-4xl sm:text-5xl font-black uppercase tracking-tight leading-[0.95] text-[#b22e3b]">
                    KELOLA <span class="italic text-[#020202]">MENU.</span>
                </h1>
                <p class="mt-3 text-sm sm:text-base font-medium text-[#020202] max-w-2xl">
                    Tambah menu baru, sesuaikan harga dan deskripsi, atau ubah status ketersediaan menu secara instan melalui tombol quick-toggle.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.orders.index') }}" class="pop-btn-outline-dark bg-[#fffaee] px-5 py-2.5 text-xs">
                    Pesanan Masuk
                </a>
                <a href="{{ route('admin.menus.create') }}" class="pop-btn-crimson px-5 py-2.5 text-xs">
                    + Tambah Menu Baru
                </a>
            </div>
        </div>
    </section>

    {{-- Admin Menus Listing (#fffaee) --}}
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($menus as $menu)
                    <article class="flex flex-col justify-between bg-[#fffaee] border-2 border-[#020202] p-5 space-y-4">
                        <div class="space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#b22e3b]">
                                        {{ $menu->category }}
                                    </span>
                                    <h2 class="mt-0.5 text-xl font-black uppercase tracking-tight text-[#020202]">
                                        {{ $menu->name }}
                                    </h2>
                                    <p class="text-xs font-bold text-[#020202]/70">
                                        Slug: {{ $menu->slug }}
                                    </p>
                                </div>

                                @if($menu->is_available)
                                    <span class="px-2.5 py-1 bg-[#b22e3b] text-[#fffaee] text-[11px] font-black uppercase tracking-wider shrink-0">
                                        Tersedia
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-[#020202] text-[#fffaee] text-[11px] font-black uppercase tracking-wider shrink-0">
                                        Tidak Tersedia
                                    </span>
                                @endif
                            </div>

                            @if($menu->description)
                                <p class="text-sm text-[#020202]/85 leading-relaxed">
                                    {{ $menu->description }}
                                </p>
                            @endif

                            <p class="text-lg font-black text-[#b22e3b]">
                                Rp {{ number_format($menu->price, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="pt-4 border-t-2 border-[#020202]/15 space-y-2.5">
                            {{-- Quick-Toggle Availability Form --}}
                            <form method="POST" action="{{ route('admin.menus.toggle-availability', $menu) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="w-full min-h-[44px] px-4 py-2 text-xs font-black uppercase tracking-wider border-2 border-[#020202] transition-colors {{ $menu->is_available ? 'bg-[#e9bfd2] text-[#020202] hover:bg-[#020202] hover:text-[#fffaee]' : 'bg-[#b22e3b] text-[#fffaee] hover:bg-[#020202]' }}">
                                    {{ $menu->is_available ? 'Matikan Ketersediaan (Set Habis)' : 'Aktifkan Ketersediaan (Set Tersedia)' }}
                                </button>
                            </form>

                            <div class="grid grid-cols-2 gap-2.5">
                                <a href="{{ route('admin.menus.edit', $menu) }}"
                                   class="pop-btn-outline-dark px-3 py-2 text-xs text-center">
                                    Ubah Menu
                                </a>

                                <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full min-h-[44px] px-3 py-2 bg-[#020202] text-[#fffaee] border-2 border-[#020202] text-xs font-black uppercase tracking-wider hover:bg-[#b22e3b] hover:border-[#b22e3b] transition-colors">
                                        Hapus Menu
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full p-8 bg-[#e9bfd2] border-2 border-[#020202] text-center space-y-3">
                        <h2 class="text-xl font-black uppercase text-[#020202]">
                            Belum Ada Data Menu
                        </h2>
                        <p class="text-sm font-medium text-[#020202]">
                            Tambahkan menu pertama Gumi Coffee melalui tombol di bawah ini.
                        </p>
                        <a href="{{ route('admin.menus.create') }}" class="pop-btn-crimson">
                            + Tambah Menu Baru
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-app-layout>
