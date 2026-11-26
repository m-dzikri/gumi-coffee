<section>
    <header class="pb-4 border-b-2 border-[#020202]/15">
        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#b22e3b]">
            IDENTITAS AKUN
        </p>
        <h2 class="mt-1 text-xl sm:text-2xl font-black uppercase tracking-tight text-[#020202]">
            Informasi Profil
        </h2>

        <p class="mt-1 text-sm font-medium text-[#020202]/80">
            Perbarui nama lengkap dan alamat email akun pelanggan Gumi Coffee Anda.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-1.5">
                Nama Lengkap
            </label>
            <input id="name"
                   name="name"
                   type="text"
                   class="pop-input"
                   value="{{ old('name', $user->name) }}"
                   required
                   autofocus
                   autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-1.5">
                Alamat Email
            </label>
            <input id="email"
                   name="email"
                   type="email"
                   class="pop-input"
                   value="{{ old('email', $user->email) }}"
                   required
                   autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-[#e9bfd2] border-2 border-[#020202]">
                    <p class="text-xs font-bold text-[#020202]">
                        Alamat email Anda belum diverifikasi.

                        <button form="send-verification" class="underline font-black text-[#b22e3b] hover:text-[#020202]">
                            Klik di sini untuk mengirim ulang tautan verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-black text-xs text-[#b22e3b]">
                            Tautan verifikasi baru telah dikirim ke alamat email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-4 pt-2">
            <button type="submit" class="pop-btn-crimson">
                Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-xs font-black uppercase tracking-wider text-[#b22e3b]"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
