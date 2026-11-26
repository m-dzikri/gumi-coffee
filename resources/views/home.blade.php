<x-app-layout>
    <x-slot name="title">Gumi Coffee Club - Beranda &amp; Brand Profile</x-slot>

    <section class="w-full bg-[#fffaee] text-[#020202] py-12 sm:py-16 lg:py-20 px-4 sm:px-6 lg:px-8 border-b-2 border-[#020202]/10">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <p class="text-xs sm:text-sm font-black uppercase tracking-[0.22em] text-[#b22e3b]">
                    GUMI COFFEE CLUB • TAKEAWAY &amp; PICK-UP BAR
                </p>

                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black uppercase tracking-tight leading-[0.95] text-[#b22e3b]">
                    <span class="block">GOOD COFFEE.</span>
                    <span class="block italic">ZERO SNOOZE.</span>
                </h1>

                <p class="text-base sm:text-lg font-medium text-[#020202] max-w-xl leading-relaxed">
                    Cerita singkat Gumi Coffee dimulai dari semangat menghadirkan kopi takeaway berkarakter kuat di {{ $location }}. Kami menyeduh biji arabika pilihan Jawa Barat secara segar setiap hari agar Anda dapat menikmati kopi terbaik tanpa antre panjang di kasir.
                </p>

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <a href="{{ route('menus.index') }}" class="pop-btn-crimson text-base px-8 py-4">
                        Lihat Menu
                    </a>
                    <a href="#cerita-kami" class="pop-btn-outline-dark text-sm px-6 py-4">
                        Cerita &amp; Info Kedai
                    </a>
                </div>

                <div class="pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-xl border-t-2 border-[#020202]/15">
                    <div>
                        <span class="block text-[11px] font-black uppercase tracking-[0.18em] text-[#b22e3b]">
                            JAM OPERASIONAL HARIAN
                        </span>
                        <span class="mt-1 block text-base sm:text-lg font-black text-[#020202]">
                            {{ $operatingHours['open'] }} - {{ $operatingHours['close'] }} WIB
                        </span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-black uppercase tracking-[0.18em] text-[#b22e3b]">
                            LOKASI PICK-UP KASIR
                        </span>
                        <span class="mt-1 block text-base sm:text-lg font-black text-[#020202]">
                            {{ $location }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 flex items-center justify-center">
                <div class="relative w-full max-w-md aspect-square flex items-center justify-center">
                    <div class="w-72 h-72 sm:w-80 sm:h-80 lg:w-96 lg:h-96 rounded-full bg-[#e9bfd2] flex items-center justify-center p-6">
                        <img src="{{ asset('images/menus/kopi-susu-gumi.svg') }}"
                             alt="Visual produk unggulan Kopi Susu Gumi"
                             width="400"
                             height="320"
                             fetchpriority="high"
                             class="w-full h-auto object-contain select-none" />
                    </div>

                    <div class="absolute -bottom-2 right-2 sm:right-4 -rotate-6 bg-[#b22e3b] text-[#fffaee] px-4 py-3 border-2 border-[#fffaee] max-w-[200px] text-center">
                        <span class="block text-[10px] font-black uppercase tracking-[0.2em] text-[#fffaee]">
                            FAST PICK-UP
                        </span>
                        <span class="mt-0.5 block text-xs sm:text-sm font-black uppercase tracking-wider leading-tight">
                            {{ $estimatedPickupText }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section aria-label="Karakter Utama Gumi Coffee" class="w-full bg-[#b22e3b] text-[#fffaee] py-4 px-4 overflow-hidden">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-around gap-x-6 gap-y-2 text-xs sm:text-sm font-black uppercase tracking-[0.2em]">
            <span class="inline-flex items-center gap-3">
                <svg class="w-4 h-4 text-[#fffaee] shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <circle cx="12" cy="12" r="5"/>
                    <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83" stroke="currentColor" stroke-width="2.5"/>
                </svg>
                <span>SINGLE ORIGIN JAWA BARAT</span>
            </span>
            <span class="inline-flex items-center gap-3">
                <svg class="w-4 h-4 text-[#fffaee] shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <circle cx="12" cy="12" r="5"/>
                    <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83" stroke="currentColor" stroke-width="2.5"/>
                </svg>
                <span>DISEDUH SEGAR SETIAP HARI</span>
            </span>
            <span class="inline-flex items-center gap-3">
                <svg class="w-4 h-4 text-[#fffaee] shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <circle cx="12" cy="12" r="5"/>
                    <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83" stroke="currentColor" stroke-width="2.5"/>
                </svg>
                <span>TAKEAWAY TANPA ANTRE</span>
            </span>
            <span class="inline-flex items-center gap-3">
                <svg class="w-4 h-4 text-[#fffaee] shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <circle cx="12" cy="12" r="5"/>
                    <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83" stroke="currentColor" stroke-width="2.5"/>
                </svg>
                <span>BUKA {{ $operatingHours['open'] }} - {{ $operatingHours['close'] }} WIB</span>
            </span>
        </div>
    </section>

    <section class="w-full bg-[#fffaee] text-[#020202] py-16 sm:py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#b22e3b]">
                    PILIHAN FAVORIT PELANGGAN
                </p>
                <h2 class="mt-2 text-3xl sm:text-5xl font-black uppercase tracking-tight text-[#b22e3b] leading-none">
                    MEET YOUR <span class="italic">MORNING PEOPLE.</span>
                </h2>
            </div>

            @if($featuredMenus->isNotEmpty())
                <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-10 items-stretch">
                    @foreach($featuredMenus as $menu)
                        <article class="flex flex-col items-center text-center group {{ $loop->first ? 'bg-[#e9bfd2]/35 border-2 border-[#020202] p-6' : 'p-4 border-2 border-transparent' }}">
                            @if($loop->first)
                                <span class="mb-2 px-3 py-1 bg-[#b22e3b] text-[#fffaee] text-[10px] font-black uppercase tracking-[0.18em]">
                                    RACIKAN UTAMA GUMI
                                </span>
                            @endif
                            <div class="w-full aspect-[4/3] bg-[#fffaee] flex items-center justify-center p-4 overflow-hidden">
                                @if($menu->image_path)
                                    <img src="{{ asset($menu->image_path) }}"
                                         alt="{{ $menu->name }}"
                                         width="400"
                                         height="320"
                                         loading="lazy"
                                         class="w-full h-56 object-contain transition-transform duration-200 group-hover:scale-105" />
                                @else
                                    <div class="w-48 h-48 rounded-full bg-[#e9bfd2] flex items-center justify-center text-xs font-black uppercase tracking-wider text-[#020202]">
                                        GUMI MENU
                                    </div>
                                @endif
                            </div>

                            <h3 class="mt-4 text-xl sm:text-2xl font-black uppercase tracking-tight text-[#020202]">
                                {{ $menu->name }}
                            </h3>

                            <p class="mt-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-[#b22e3b]">
                                {{ strtoupper($menu->category) }} • FRESH BREW • TAKEAWAY
                            </p>

                            @if($menu->description)
                                <p class="mt-2 text-sm text-[#020202]/80 max-w-xs leading-relaxed">
                                    {{ $menu->description }}
                                </p>
                            @endif

                            <p class="mt-3 text-base font-black text-[#020202]">
                                Rp {{ number_format($menu->price, 0, ',', '.') }}
                            </p>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mt-10 text-center py-8 bg-[#e9bfd2] border-2 border-[#020202]">
                    <p class="text-sm font-bold text-[#020202]">
                        Daftar menu unggulan siap dilihat langsung di halaman Katalog Menu.
                    </p>
                </div>
            @endif

            <div class="mt-12 text-center">
                <a href="{{ route('menus.index') }}" class="pop-btn-crimson">
                    Jelajahi Seluruh Menu
                </a>
            </div>
        </div>
    </section>

    <section class="w-full bg-[#020202] text-[#fffaee] py-16 sm:py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-7 space-y-6">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#e9bfd2]">
                    PILIH KARAKTER RACIKANMU
                </p>
                <h2 class="text-4xl sm:text-5xl lg:text-6xl font-black uppercase tracking-tight leading-[0.95] text-[#fffaee]">
                    <span class="block">WHAT'S YOUR</span>
                    <span class="block italic text-[#e9bfd2]">TYPE?</span>
                </h2>
                <p class="text-sm sm:text-base text-[#fffaee]/85 max-w-lg leading-relaxed">
                    Dari espresso klasik yang tegas, racikan non-kopi yang lembut, hingga pastry mentega hangat. Pilih kategori di bawah untuk langsung menuju bagian katalog yang Anda inginkan.
                </p>

                <div class="pt-2 flex flex-wrap gap-3 sm:gap-4">
                    @foreach($categories as $category)
                        <a href="{{ route('menus.index') }}#kategori-{{ \Illuminate\Support\Str::slug($category) }}"
                           class="pop-btn-outline-light">
                            {{ strtoupper($category) }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-5 flex items-center justify-center">
                <div class="w-full max-w-xs sm:max-w-sm p-6 flex flex-col items-center">
                    <svg class="w-56 h-48" viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <line x1="110" y1="36" x2="110" y2="12" stroke="#e9bfd2" stroke-width="5"/>
                        <line x1="68" y1="48" x2="50" y2="30" stroke="#e9bfd2" stroke-width="5"/>
                        <line x1="152" y1="48" x2="170" y2="30" stroke="#e9bfd2" stroke-width="5"/>
                        <line x1="46" y1="80" x2="22" y2="72" stroke="#e9bfd2" stroke-width="5"/>
                        <line x1="174" y1="80" x2="198" y2="72" stroke="#e9bfd2" stroke-width="5"/>
                        <path d="M52 88 H168 V126 C168 158 142 178 110 178 C78 178 52 158 52 126 Z" fill="#b22e3b"/>
                        <path d="M78 140 A32 32 0 0 1 142 140 Z" fill="#fffaee"/>
                        <path d="M168 98 H184 C196 98 204 106 204 118 C204 130 196 138 184 138 H168" stroke="#b22e3b" stroke-width="12" fill="none"/>
                        <rect x="36" y="180" width="148" height="10" rx="5" fill="#e9bfd2"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <section id="cerita-kami" class="w-full bg-[#e9bfd2] text-[#020202]">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12">
            <div class="lg:col-span-6 bg-[#020202] text-[#fffaee] p-8 sm:p-12 lg:p-16 flex flex-col justify-between min-h-[340px]">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-[0.22em] text-[#e9bfd2]">
                        EST. BANDUNG • BRAGA CORRIDOR
                    </span>
                    <span class="px-3 py-1 bg-[#b22e3b] text-[#fffaee] text-xs font-black uppercase tracking-wider">
                        100% ARABIKA
                    </span>
                </div>

                <div class="my-8 flex items-center justify-center">
                    <svg class="w-64 h-52" viewBox="0 0 280 220" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="140" cy="115" r="88" fill="#b22e3b"/>
                        <path d="M98 76 L182 76 L156 132 L124 132 Z" fill="#fffaee"/>
                        <rect x="108" y="132" width="64" height="10" fill="#020202"/>
                        <rect x="112" y="142" width="56" height="48" rx="6" fill="#e9bfd2"/>
                        <path d="M126 52 Q140 28 154 52" stroke="#fffaee" stroke-width="5" fill="none"/>
                    </svg>
                </div>

                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#e9bfd2]">
                    GUMI COFFEE CLUB • ROASTED &amp; BREWED IN BANDUNG
                </p>
            </div>

            <div class="lg:col-span-6 bg-[#e9bfd2] text-[#020202] p-8 sm:p-12 lg:p-16 flex flex-col justify-center space-y-6">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#020202]">
                    CERITA SINGKAT GUMI COFFEE
                </p>

                <h2 class="text-4xl sm:text-5xl font-black uppercase tracking-tight leading-[0.95] text-[#b22e3b]">
                    <span class="block">LESS GUESS.</span>
                    <span class="block italic text-[#020202]">MORE YES.</span>
                </h2>

                <p class="text-sm sm:text-base font-medium text-[#020202] leading-relaxed">
                    Gumi Coffee lahir untuk menyederhanakan ritual kopi pagi Anda. Tanpa daftar menu yang membingungkan dan tanpa antrean kasir yang menguras waktu: pesan menu favorit Anda secara daring, dan ambil langsung di bar kami dalam kondisi segar.
                </p>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t-2 border-[#020202]/20">
                    <div class="bg-[#fffaee] p-4">
                        <dt class="text-[11px] font-black uppercase tracking-[0.18em] text-[#b22e3b]">
                            LOKASI KEDAI
                        </dt>
                        <dd class="mt-1 text-sm sm:text-base font-black text-[#020202]">
                            {{ $location }}
                        </dd>
                    </div>
                    <div class="bg-[#fffaee] p-4">
                        <dt class="text-[11px] font-black uppercase tracking-[0.18em] text-[#b22e3b]">
                            JAM BUKA OPERASIONAL
                        </dt>
                        <dd class="mt-1 text-sm sm:text-base font-black text-[#020202]">
                            {{ $operatingHours['open'] }} - {{ $operatingHours['close'] }} WIB
                        </dd>
                    </div>
                </dl>

                <div class="pt-2">
                    <a href="{{ route('menus.index') }}" class="pop-btn-crimson">
                        Pesan Takeaway Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
