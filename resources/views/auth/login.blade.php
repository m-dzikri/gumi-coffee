<x-guest-layout>
    <x-slot name="title">Masuk - Gumi Coffee Club</x-slot>

    <div class="mb-6">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-[#b22e3b]">
            GUMI COFFEE CLUB
        </p>
        <h1 class="mt-1 text-3xl font-black uppercase tracking-tight text-[#020202]">
            Masuk Akun
        </h1>
        <p class="mt-1 text-sm text-[#020202]/80">
            Masuk untuk melanjutkan pemesanan takeaway kopi favorit Anda.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-1.5">
                Alamat Email
            </label>
            <input id="email"
                   class="pop-input"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autofocus
                   autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-1.5">
                Kata Sandi
            </label>
            <input id="password"
                   class="pop-input"
                   type="password"
                   name="password"
                   required
                   autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2">
            <label for="remember_me" class="min-h-[44px] inline-flex items-center gap-2">
                <input id="remember_me"
                       type="checkbox"
                       class="w-4 h-4 border-2 border-[#020202] text-[#b22e3b] focus:ring-[#b22e3b]"
                       name="remember">
                <span class="text-xs font-bold text-[#020202]">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="min-h-[44px] inline-flex items-center text-xs font-bold underline text-[#b22e3b] hover:text-[#020202]" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full pop-btn-crimson">
                Masuk Sekarang
            </button>
        </div>

        <p class="text-center text-xs font-bold text-[#020202] pt-2">
            Belum punya akun?
            <a href="{{ route('register') }}" class="min-h-[44px] inline-flex items-center text-[#b22e3b] underline hover:text-[#020202]">Daftar sebagai pelanggan</a>
        </p>
    </form>
</x-guest-layout>
