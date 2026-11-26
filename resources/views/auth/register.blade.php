<x-guest-layout>
    <x-slot name="title">Daftar Pelanggan - Gumi Coffee Club</x-slot>

    <div class="mb-6">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-[#b22e3b]">
            GUMI COFFEE CLUB
        </p>
        <h1 class="mt-1 text-3xl font-black uppercase tracking-tight text-[#020202]">
            Daftar Akun
        </h1>
        <p class="mt-1 text-sm text-[#020202]/80">
            Buat akun pelanggan Gumi Coffee untuk memesan takeaway lebih cepat.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-1.5">
                Nama Lengkap
            </label>
            <input id="name"
                   class="pop-input"
                   type="text"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   autofocus
                   autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

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
                   autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-1.5">
                Konfirmasi Kata Sandi
            </label>
            <input id="password_confirmation"
                   class="pop-input"
                   type="password"
                   name="password_confirmation"
                   required
                   autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full pop-btn-crimson">
                Daftar Akun Pelanggan
            </button>
        </div>

        <p class="text-center text-xs font-bold text-[#020202] pt-2">
            Sudah terdaftar?
            <a href="{{ route('login') }}" class="text-[#b22e3b] underline">Masuk di sini</a>
        </p>
    </form>
</x-guest-layout>
