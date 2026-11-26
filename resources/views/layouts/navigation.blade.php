@php
    $openHour = config('gumi.operating_hours.open', '08:00');
    $closeHour = config('gumi.operating_hours.close', '22:00');
    $location = config('gumi.location', 'Jl. Braga No. 42, Bandung');
    $cartCount = collect(session('cart', []))->sum('quantity');
@endphp
<header x-data="{ mobileOpen: false }" class="w-full">
    <div class="w-full bg-[#e9bfd2] text-[#020202] border-b border-[#020202]/15 px-4 py-2">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-center text-[11px] sm:text-xs font-black uppercase tracking-[0.16em]">
            <span>FRESHLY BREWED TAKEAWAY</span>
            <span aria-hidden="true">•</span>
            <span>BUKA SETIAP HARI {{ $openHour }} - {{ $closeHour }} WIB</span>
            <span class="hidden md:inline" aria-hidden="true">•</span>
            <span class="hidden md:inline">{{ $location }}</span>
        </div>
    </div>

    <nav aria-label="Navigasi Utama" class="w-full bg-[#b22e3b] text-[#fffaee]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between min-h-[72px] py-3 gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-baseline gap-2.5 group focus-visible:outline-[#fffaee]">
                    <span class="text-3xl sm:text-4xl font-black tracking-tighter lowercase leading-none text-[#fffaee]">
                        gumi
                    </span>
                    <span class="text-[11px] sm:text-xs font-black uppercase tracking-[0.22em] text-[#fffaee] border-l-2 border-[#fffaee]/50 pl-2.5 leading-tight">
                        COFFEE<br class="sm:hidden"> CLUB
                    </span>
                </a>

                <div class="hidden md:flex items-center gap-5 lg:gap-7">
                    <a href="{{ route('home') }}"
                       class="min-h-[44px] inline-flex items-center text-xs lg:text-sm font-black uppercase tracking-[0.14em] text-[#fffaee] transition-colors {{ request()->routeIs('home') ? 'underline decoration-2 underline-offset-8' : 'hover:underline hover:decoration-2 hover:underline-offset-8' }}">
                        Beranda
                    </a>
                    <a href="{{ route('menus.index') }}"
                       class="min-h-[44px] inline-flex items-center text-xs lg:text-sm font-black uppercase tracking-[0.14em] text-[#fffaee] transition-colors {{ request()->routeIs('menus.*') ? 'underline decoration-2 underline-offset-8' : 'hover:underline hover:decoration-2 hover:underline-offset-8' }}">
                        Lihat Menu
                    </a>
                    <a href="{{ route('home') }}#cerita-kami"
                       class="min-h-[44px] inline-flex items-center text-xs lg:text-sm font-black uppercase tracking-[0.14em] text-[#fffaee] hover:underline hover:decoration-2 hover:underline-offset-8 transition-colors">
                        Cerita Kami
                    </a>
                    <a href="{{ route('cart.index') }}"
                       class="min-h-[44px] inline-flex items-center gap-2 text-xs lg:text-sm font-black uppercase tracking-[0.14em] text-[#fffaee] transition-colors {{ request()->routeIs('cart.*') || request()->routeIs('checkout.*') ? 'underline decoration-2 underline-offset-8' : 'hover:underline hover:decoration-2 hover:underline-offset-8' }}">
                        <span>Keranjang</span>
                        <span class="px-2 py-0.5 bg-[#e9bfd2] text-[#020202] text-[11px] font-black">
                            {{ $cartCount }}
                        </span>
                    </a>
                    @auth
                        <a href="{{ route('orders.index') }}"
                           class="min-h-[44px] inline-flex items-center text-xs lg:text-sm font-black uppercase tracking-[0.14em] text-[#fffaee] transition-colors {{ request()->routeIs('orders.*') ? 'underline decoration-2 underline-offset-8' : 'hover:underline hover:decoration-2 hover:underline-offset-8' }}">
                            Pesanan Saya
                        </a>
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.orders.index') }}"
                               class="min-h-[44px] inline-flex items-center text-xs lg:text-sm font-black uppercase tracking-[0.14em] text-[#fffaee] transition-colors {{ request()->routeIs('admin.orders.*') ? 'underline decoration-2 underline-offset-8' : 'hover:underline hover:decoration-2 hover:underline-offset-8' }}">
                                Pesanan Admin
                            </a>
                            <a href="{{ route('admin.menus.index') }}"
                               class="min-h-[44px] inline-flex items-center text-xs lg:text-sm font-black uppercase tracking-[0.14em] text-[#fffaee] transition-colors {{ request()->routeIs('admin.menus.*') ? 'underline decoration-2 underline-offset-8' : 'hover:underline hover:decoration-2 hover:underline-offset-8' }}">
                                Kelola Menu
                            </a>
                        @endif
                    @endauth

                    <div class="h-5 w-px bg-[#fffaee]/40" aria-hidden="true"></div>

                    @auth
                        <div class="flex items-center gap-3">
                            <a href="{{ route('profile.edit') }}"
                               class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 bg-[#020202] text-[#fffaee] text-xs font-black uppercase tracking-wider hover:bg-[#e9bfd2] hover:text-[#020202] transition-colors">
                                <span>{{ Auth::user()->name }}</span>
                                <span class="px-1.5 py-0.5 bg-[#b22e3b] text-[#fffaee] text-[10px]">
                                    {{ strtoupper(Auth::user()->role) }}
                                </span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="min-h-[44px] inline-flex items-center px-4 py-2 border-2 border-[#fffaee] text-[#fffaee] text-xs font-black uppercase tracking-wider hover:bg-[#fffaee] hover:text-[#020202] transition-colors">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-3">
                            <a href="{{ route('login') }}"
                               class="min-h-[44px] inline-flex items-center px-4 py-2 border-2 border-[#fffaee] text-[#fffaee] text-xs font-black uppercase tracking-wider hover:bg-[#fffaee] hover:text-[#020202] transition-colors">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}"
                               class="min-h-[44px] inline-flex items-center px-4 py-2 bg-[#fffaee] text-[#020202] border-2 border-[#fffaee] text-xs font-black uppercase tracking-wider hover:bg-[#e9bfd2] hover:border-[#e9bfd2] transition-colors">
                                Daftar
                            </a>
                        </div>
                    @endauth
                </div>

                <div class="flex md:hidden">
                    <button type="button"
                            @click="mobileOpen = !mobileOpen"
                            :aria-expanded="mobileOpen.toString()"
                            aria-controls="mobile-nav-menu"
                            aria-label="Buka menu navigasi"
                            class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center border-2 border-[#fffaee] text-[#fffaee] px-3 py-2 text-xs font-black uppercase tracking-wider hover:bg-[#fffaee] hover:text-[#020202] transition-colors">
                        <span x-show="!mobileOpen">MENU</span>
                        <span x-show="mobileOpen" x-cloak>TUTUP</span>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-nav-menu"
             x-show="mobileOpen"
             x-cloak
             class="md:hidden bg-[#020202] text-[#fffaee] border-t-2 border-[#fffaee]/20 px-4 pt-4 pb-6 space-y-3">
            <a href="{{ route('home') }}"
               class="block min-h-[44px] py-2.5 px-3 font-black uppercase tracking-wider text-sm {{ request()->routeIs('home') ? 'bg-[#b22e3b] text-[#fffaee]' : 'text-[#fffaee] hover:bg-[#b22e3b]/40' }}">
                Beranda
            </a>
            <a href="{{ route('menus.index') }}"
               class="block min-h-[44px] py-2.5 px-3 font-black uppercase tracking-wider text-sm {{ request()->routeIs('menus.*') ? 'bg-[#b22e3b] text-[#fffaee]' : 'text-[#fffaee] hover:bg-[#b22e3b]/40' }}">
                Lihat Menu
            </a>
            <a href="{{ route('home') }}#cerita-kami"
               @click="mobileOpen = false"
               class="block min-h-[44px] py-2.5 px-3 font-black uppercase tracking-wider text-sm text-[#fffaee] hover:bg-[#b22e3b]/40">
                Cerita Kami
            </a>
            <a href="{{ route('cart.index') }}"
               class="flex items-center justify-between min-h-[44px] py-2.5 px-3 font-black uppercase tracking-wider text-sm {{ request()->routeIs('cart.*') || request()->routeIs('checkout.*') ? 'bg-[#b22e3b] text-[#fffaee]' : 'text-[#fffaee] hover:bg-[#b22e3b]/40' }}">
                <span>Keranjang</span>
                <span class="px-2 py-0.5 bg-[#e9bfd2] text-[#020202] text-xs font-black">{{ $cartCount }}</span>
            </a>
            @auth
                <a href="{{ route('orders.index') }}"
                   class="block min-h-[44px] py-2.5 px-3 font-black uppercase tracking-wider text-sm {{ request()->routeIs('orders.*') ? 'bg-[#b22e3b] text-[#fffaee]' : 'text-[#fffaee] hover:bg-[#b22e3b]/40' }}">
                    Pesanan Saya
                </a>
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.orders.index') }}"
                       class="block min-h-[44px] py-2.5 px-3 font-black uppercase tracking-wider text-sm {{ request()->routeIs('admin.orders.*') ? 'bg-[#b22e3b] text-[#fffaee]' : 'text-[#fffaee] hover:bg-[#b22e3b]/40' }}">
                        Pesanan Admin
                    </a>
                    <a href="{{ route('admin.menus.index') }}"
                       class="block min-h-[44px] py-2.5 px-3 font-black uppercase tracking-wider text-sm {{ request()->routeIs('admin.menus.*') ? 'bg-[#b22e3b] text-[#fffaee]' : 'text-[#fffaee] hover:bg-[#b22e3b]/40' }}">
                        Kelola Menu
                    </a>
                @endif
            @endauth

            <div class="pt-3 border-t border-[#fffaee]/20 space-y-2">
                @auth
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center justify-between min-h-[44px] py-2.5 px-3 bg-[#e9bfd2] text-[#020202] font-black uppercase text-xs tracking-wider">
                        <span>Profil: {{ Auth::user()->name }}</span>
                        <span class="px-2 py-0.5 bg-[#020202] text-[#fffaee]">{{ strtoupper(Auth::user()->role) }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full min-h-[44px] py-2.5 px-3 border-2 border-[#fffaee] text-[#fffaee] font-black uppercase text-xs tracking-wider text-center hover:bg-[#fffaee] hover:text-[#020202]">
                            Keluar
                        </button>
                    </form>
                @else
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('login') }}"
                           class="min-h-[44px] inline-flex items-center justify-center border-2 border-[#fffaee] text-[#fffaee] font-black uppercase text-xs tracking-wider">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}"
                           class="min-h-[44px] inline-flex items-center justify-center bg-[#e9bfd2] text-[#020202] font-black uppercase text-xs tracking-wider">
                            Daftar
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>
</header>
