<x-app-layout>
    <x-slot name="title">Tambah Menu Baru - Gumi Coffee Club</x-slot>

    {{-- Create Menu Header Band (#e9bfd2) --}}
    <section class="w-full bg-[#e9bfd2] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8 border-b-2 border-[#020202]/15">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#b22e3b]">
                    GUMI COFFEE CLUB • ADMIN CATALOG MANAGEMENT
                </p>
                <h1 class="mt-2 text-4xl sm:text-5xl font-black uppercase tracking-tight leading-[0.95] text-[#b22e3b]">
                    TAMBAH <span class="italic text-[#020202]">MENU.</span>
                </h1>
                <p class="mt-3 text-sm sm:text-base font-medium text-[#020202]">
                    Slug unik akan dibuat otomatis dari nama menu. Harga minimal Rp 1.000.
                </p>
            </div>

            <a href="{{ route('admin.menus.index') }}" class="pop-btn-outline-dark bg-[#fffaee] px-5 py-2.5 text-xs">
                Kembali ke Daftar Menu
            </a>
        </div>
    </section>

    {{-- Create Menu Form (#fffaee) --}}
    <section class="w-full bg-[#fffaee] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            @if($errors->any())
                <div role="alert" class="mb-6 p-4 bg-[#b22e3b] text-[#fffaee] border-2 border-[#020202] space-y-1">
                    @foreach($errors->all() as $error)
                        <p class="text-sm font-bold">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.menus.store') }}" class="bg-[#fffaee] border-2 border-[#020202] p-6 sm:p-8 space-y-6">
                @csrf

                <div>
                    <label for="menu-name" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-2">
                        Nama Menu
                    </label>
                    <input id="menu-name"
                           name="name"
                           type="text"
                           value="{{ old('name') }}"
                           required
                           maxlength="255"
                           placeholder="Contoh: Kopi Susu Gumi"
                           class="pop-input" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="menu-category" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-2">
                            Kategori Menu
                        </label>
                        <select id="menu-category" name="category" required class="pop-input">
                            @foreach($categories as $category)
                                <option value="{{ $category }}" @selected(old('category') === $category)>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="menu-price" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-2">
                            Harga Satuan (Rupiah, minimal 1000)
                        </label>
                        <input id="menu-price"
                               name="price"
                               type="number"
                               min="1000"
                               step="500"
                               value="{{ old('price', 20000) }}"
                               required
                               class="pop-input" />
                    </div>
                </div>

                <div>
                    <label for="menu-image-path" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-2">
                        Path Gambar Statis / URL Gambar (Opsional)
                    </label>
                    <input id="menu-image-path"
                           name="image_path"
                           type="text"
                           value="{{ old('image_path') }}"
                           maxlength="2048"
                           placeholder="Contoh: images/menus/gumi-signature.svg"
                           class="pop-input" />
                </div>

                <div>
                    <label for="menu-description" class="block text-xs font-black uppercase tracking-wider text-[#020202] mb-2">
                        Deskripsi Menu (Opsional)
                    </label>
                    <textarea id="menu-description"
                              name="description"
                              rows="3"
                              maxlength="1000"
                              placeholder="Deskripsi singkat cita rasa menu..."
                              class="pop-input">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center gap-3 p-4 bg-[#e9bfd2]/45 border-2 border-[#020202]">
                    <input type="hidden" name="is_available" value="0" />
                    <input id="menu-is-available"
                           name="is_available"
                           type="checkbox"
                           value="1"
                           @checked(old('is_available', '1') == '1')
                           class="w-5 h-5 accent-[#b22e3b] border-2 border-[#020202]" />
                    <label for="menu-is-available" class="text-sm font-black uppercase tracking-wider text-[#020202]">
                        Menu Tersedia untuk Dipesan (is_available)
                    </label>
                </div>

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <button type="submit" class="pop-btn-crimson">
                        Simpan Menu
                    </button>
                    <a href="{{ route('admin.menus.index') }}" class="pop-btn-outline-dark">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </section>
</x-app-layout>
