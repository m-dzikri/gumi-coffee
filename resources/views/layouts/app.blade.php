<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Gumi Coffee') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen flex flex-col bg-[#fffaee] text-[#020202] antialiased selection:bg-[#b22e3b] selection:text-[#fffaee]">
        @include('layouts.navigation')

        <main class="flex-1 w-full">
            {{ $slot }}
        </main>

        {{-- Pre-Footer Sunburst Tagline (#fffaee) & Solid Footer (#b22e3b) --}}
        <footer class="w-full mt-auto">
            {{-- Pre-Footer Sunburst Tagline Strip --}}
            <div class="w-full bg-[#fffaee] text-[#020202] border-t-2 border-[#020202]/15 py-6 px-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-5">
                        {{-- Rising Sun / Sunburst Graphic --}}
                        <svg class="w-16 h-10 text-[#b22e3b] shrink-0" viewBox="0 0 80 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M16 44 A24 24 0 0 1 64 44 Z" fill="currentColor"/>
                            <line x1="40" y1="12" x2="40" y2="2" stroke="currentColor" stroke-width="4"/>
                            <line x1="20" y1="18" x2="12" y2="10" stroke="currentColor" stroke-width="4"/>
                            <line x1="60" y1="18" x2="68" y2="10" stroke="currentColor" stroke-width="4"/>
                            <line x1="10" y1="34" x2="2" y2="30" stroke="currentColor" stroke-width="4"/>
                            <line x1="70" y1="34" x2="78" y2="30" stroke="currentColor" stroke-width="4"/>
                            <line x1="4" y1="44" x2="76" y2="44" stroke="#020202" stroke-width="3"/>
                        </svg>
                        <div class="h-8 w-0.5 bg-[#020202]" aria-hidden="true"></div>
                        <p class="text-sm sm:text-base font-black italic uppercase tracking-wider text-[#b22e3b]">
                            SERIOUS ABOUT COFFEE. NOT OURSELVES.
                        </p>
                    </div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-[#020202]">
                        {{ config('gumi.estimated_pickup_text', 'Estimasi siap 10-15 menit') }}
                    </p>
                </div>
            </div>

            {{-- Solid Crimson Footer (#b22e3b) --}}
            <div class="w-full bg-[#b22e3b] text-[#fffaee] py-10 px-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                    <div>
                        <div class="inline-flex items-baseline gap-2.5">
                            <span class="text-3xl font-black tracking-tighter lowercase text-[#fffaee]">gumi</span>
                            <span class="text-xs font-black uppercase tracking-[0.22em] text-[#e9bfd2]">COFFEE CLUB</span>
                        </div>
                        <p class="mt-3 text-sm text-[#fffaee]/90 max-w-sm leading-relaxed">
                            Kedai kopi takeaway bergaya pop-editorial di jantung Braga. Racikan biji pilihan Jawa Barat, diseduh segar tanpa antre panjang.
                        </p>
                    </div>

                    <div>
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#e9bfd2]">
                            LOKASI & JAM BUKA
                        </h2>
                        <p class="mt-3 text-sm font-bold text-[#fffaee]">
                            {{ config('gumi.location') }}
                        </p>
                        <p class="mt-1 text-sm font-bold text-[#fffaee]">
                            Jam Operasional: {{ config('gumi.operating_hours.open') }} - {{ config('gumi.operating_hours.close') }} WIB
                        </p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-[#e9bfd2]">
                            {{ config('gumi.estimated_pickup_text') }}
                        </p>
                    </div>

                    <div class="md:text-right">
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#e9bfd2]">
                            NAVIGASI CEPAT
                        </h2>
                        <div class="mt-3 flex flex-wrap md:justify-end gap-4">
                            <a href="{{ route('home') }}" class="min-h-[44px] inline-flex items-center text-xs font-black uppercase tracking-wider text-[#fffaee] hover:text-[#e9bfd2] underline underline-offset-4">
                                Beranda
                            </a>
                            <a href="{{ route('menus.index') }}" class="min-h-[44px] inline-flex items-center text-xs font-black uppercase tracking-wider text-[#fffaee] hover:text-[#e9bfd2] underline underline-offset-4">
                                Katalog Menu
                            </a>
                            @guest
                                <a href="{{ route('login') }}" class="min-h-[44px] inline-flex items-center text-xs font-black uppercase tracking-wider text-[#fffaee] hover:text-[#e9bfd2] underline underline-offset-4">
                                    Masuk
                                </a>
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
