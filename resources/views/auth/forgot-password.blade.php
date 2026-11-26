<x-guest-layout>
    <x-slot name="title">Lupa Kata Sandi - Gumi Coffee Club</x-slot>

    <div class="mb-6">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-[#b22e3b]">
            GUMI COFFEE CLUB
        </p>
        <h1 class="mt-1 text-3xl font-black uppercase tracking-tight text-[#020202]">
            Atur Ulang Sandi
        </h1>
        <p class="mt-2 text-sm text-[#020202]/80">
            Masukkan alamat email akun Anda untuk menerima tautan pengaturan ulang kata sandi.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-1.5">
                Alamat Email
            </label>
            <input id="email" class="pop-input" type="email" name="email" value="{{ old('email') }}" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full pop-btn-crimson">
                Kirim Tautan Reset Sandi
            </button>
        </div>
    </form>
</x-guest-layout>
