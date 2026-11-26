<x-app-layout>
    <x-slot name="title">Profil Pelanggan - Gumi Coffee Club</x-slot>

    <section class="w-full bg-[#e9bfd2] text-[#020202] py-10 px-4 sm:px-6 lg:px-8 border-b-2 border-[#020202]/15">
        <div class="max-w-4xl mx-auto">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-[#020202]">
                GUMI COFFEE CLUB • PENGATURAN AKUN
            </p>
            <h1 class="mt-1 text-3xl sm:text-4xl font-black uppercase tracking-tight text-[#b22e3b]">
                Profil <span class="italic text-[#020202]">Pelanggan.</span>
            </h1>
            <p class="mt-2 text-sm sm:text-base font-medium text-[#020202]">
                Perbarui informasi identitas dan kata sandi akun Gumi Coffee Anda.
            </p>
        </div>
    </section>

    <section class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="space-y-8">
            <div class="p-6 sm:p-8 bg-[#fffaee] border-2 border-[#020202]">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-[#fffaee] border-2 border-[#020202]">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
