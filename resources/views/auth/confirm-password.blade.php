<x-guest-layout>
    <x-slot name="title">Konfirmasi Kata Sandi - Gumi Coffee Club</x-slot>

    <div class="mb-6">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-[#b22e3b]">
            GUMI COFFEE CLUB
        </p>
        <h1 class="mt-1 text-3xl font-black uppercase tracking-tight text-[#020202]">
            Konfirmasi Sandi
        </h1>
        <p class="mt-2 text-sm text-[#020202]/80">
            Area ini memerlukan verifikasi keamanan. Masukkan kata sandi Anda sebelum melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

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

        <div class="pt-2">
            <button type="submit" class="w-full pop-btn-crimson">
                Konfirmasi
            </button>
        </div>
    </form>
</x-guest-layout>
