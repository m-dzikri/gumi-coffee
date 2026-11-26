<x-guest-layout>
    <x-slot name="title">Verifikasi Email - Gumi Coffee Club</x-slot>

    <div class="mb-6">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-[#b22e3b]">
            GUMI COFFEE CLUB
        </p>
        <h1 class="mt-1 text-3xl font-black uppercase tracking-tight text-[#020202]">
            Verifikasi Email
        </h1>
        <p class="mt-2 text-sm text-[#020202]/80">
            Terima kasih telah mendaftar. Silakan verifikasi alamat email Anda melalui tautan yang baru saja kami kirimkan.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3 bg-[#e9bfd2] border-2 border-[#020202] font-bold text-xs text-[#020202]">
            Tautan verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.
        </div>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <button type="submit" class="pop-btn-crimson">
                Kirim Ulang Email Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="pop-btn-outline-dark">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
