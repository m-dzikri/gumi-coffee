<section>
    <header class="pb-4 border-b-2 border-[#020202]/15">
        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#b22e3b]">
            KEAMANAN AKUN
        </p>
        <h2 class="mt-1 text-xl sm:text-2xl font-black uppercase tracking-tight text-[#020202]">
            Ubah Kata Sandi
        </h2>

        <p class="mt-1 text-sm font-medium text-[#020202]/80">
            Gunakan kombinasi kata sandi yang kuat untuk menjaga keamanan akun Gumi Coffee Anda.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-1.5">
                Kata Sandi Saat Ini
            </label>
            <input id="update_password_current_password"
                   name="current_password"
                   type="password"
                   class="pop-input"
                   autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-1.5">
                Kata Sandi Baru
            </label>
            <input id="update_password_password"
                   name="password"
                   type="password"
                   class="pop-input"
                   autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-1.5">
                Konfirmasi Kata Sandi Baru
            </label>
            <input id="update_password_password_confirmation"
                   name="password_confirmation"
                   type="password"
                   class="pop-input"
                   autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center gap-4 pt-2">
            <button type="submit" class="pop-btn-crimson">
                Perbarui Kata Sandi
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-xs font-black uppercase tracking-wider text-[#b22e3b]"
                >Kata sandi diperbarui.</p>
            @endif
        </div>
    </form>
</section>
