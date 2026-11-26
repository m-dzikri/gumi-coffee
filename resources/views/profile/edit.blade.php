<x-app-layout>
    <x-slot name="title">Profil Akun - Gumi Coffee</x-slot>

    <section class="py-10 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
        <div class="mb-8 border-2 border-[#020202] bg-[#e9bfd2] p-6 shadow-[4px_4px_0px_#020202]">
            <h1 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-[#020202]">
                Profil Pelanggan
            </h1>
            <p class="mt-2 text-sm sm:text-base font-medium text-[#020202]">
                Perbarui informasi identitas dan kata sandi akun Gumi Coffee Anda.
            </p>
        </div>

        <div class="space-y-8">
            <div class="p-6 sm:p-8 bg-[#fffaee] border-2 border-[#020202] shadow-[4px_4px_0px_#020202]">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-[#fffaee] border-2 border-[#020202] shadow-[4px_4px_0px_#020202]">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
