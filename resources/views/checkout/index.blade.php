<x-app-layout>
    <x-slot name="title">Checkout Takeaway - Gumi Coffee Club</x-slot>

    <section class="w-full bg-[#e9bfd2] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8 border-b-2 border-[#020202]/15">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#020202]">
                    GUMI COFFEE CLUB • TAKEAWAY CHECKOUT
                </p>
                <h1 class="mt-2 text-4xl sm:text-5xl font-black uppercase tracking-tight leading-[0.95] text-[#b22e3b]">
                    CHECKOUT <span class="italic text-[#020202]">TAKEAWAY.</span>
                </h1>
                <p class="mt-3 text-sm sm:text-base font-medium text-[#020202] max-w-2xl">
                    Seluruh pemesanan berlaku khusus untuk <strong>Takeaway / Pick-up di kasir</strong> ({{ $location }}). {{ $estimatedPickupText }}.
                </p>
            </div>

            <a href="{{ route('cart.index') }}" class="pop-btn-outline-dark bg-[#fffaee]">
                Kembali ke Keranjang
            </a>
        </div>
    </section>

    <section class="w-full bg-[#fffaee] text-[#020202] py-10 sm:py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">
            @if($errors->any())
                <div role="alert" class="p-4 bg-[#b22e3b] text-[#fffaee] border-2 border-[#020202] space-y-1">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-[#fffaee]">CHECKOUT TIDAK DAPAT DIPROSES</p>
                    @foreach($errors->all() as $error)
                        <p class="text-sm font-bold">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-7 bg-[#fffaee] border-2 border-[#020202] p-6 sm:p-8 space-y-6">
                    <div class="p-4 bg-[#e9bfd2] border-2 border-[#020202] space-y-1">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-[#020202]">
                            LAYANAN KHUSUS TAKEAWAY / PICK-UP DI KASIR
                        </p>
                        <p class="text-sm font-bold text-[#020202]">
                            {{ $estimatedPickupText }} • Jam Operasional: {{ $operatingHours['open'] }} - {{ $operatingHours['close'] }} WIB
                        </p>
                        <p class="text-xs font-medium text-[#020202]/80">
                            Pesanan disiapkan langsung setelah dikonfirmasi. Ambil pesanan di kasir {{ $location }} dengan menyebutkan kode pesanan Anda.
                        </p>
                    </div>

                    <form x-data="{ submitting: false }"
                          @submit="submitting = true"
                          method="POST"
                          action="{{ route('checkout.store') }}"
                          class="space-y-6">
                        @csrf

                        <fieldset class="space-y-3">
                            <legend class="text-sm font-black uppercase tracking-wider text-[#020202]">
                                Pilih Metode Pembayaran
                            </legend>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="relative bg-[#fffaee] border-2 border-[#020202] p-4 hover:border-[#b22e3b] transition-colors">
                                    <div class="flex items-start gap-3">
                                        <input id="payment_method_cash"
                                               type="radio"
                                               name="payment_method"
                                               value="cash"
                                               {{ old('payment_method', 'cash') === 'cash' ? 'checked' : '' }}
                                               required
                                               class="mt-1 h-5 w-5 accent-[#b22e3b]" />
                                        <label for="payment_method_cash" class="block flex-1 min-h-[44px]">
                                            <span class="block text-sm font-black uppercase tracking-wider text-[#020202]">
                                                Tunai di Kasir (cash)
                                            </span>
                                            <span class="mt-1 block text-xs font-medium text-[#020202]/80">
                                                Bayar langsung di kasir saat mengambil pesanan takeaway Anda.
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="relative bg-[#fffaee] border-2 border-[#020202] p-4 hover:border-[#b22e3b] transition-colors">
                                    <div class="flex items-start gap-3">
                                        <input id="payment_method_qris_dummy"
                                               type="radio"
                                               name="payment_method"
                                               value="qris_dummy"
                                               {{ old('payment_method') === 'qris_dummy' ? 'checked' : '' }}
                                               required
                                               class="mt-1 h-5 w-5 accent-[#b22e3b]" />
                                        <label for="payment_method_qris_dummy" class="block flex-1 min-h-[44px]">
                                            <span class="block text-sm font-black uppercase tracking-wider text-[#020202]">
                                                QRIS Simulasi (qris_dummy)
                                            </span>
                                            <span class="mt-1 block text-xs font-medium text-[#020202]/80">
                                                Simulasi pembayaran QRIS instan pada halaman detail pesanan.
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="space-y-2">
                            <label for="notes" class="block text-sm font-black uppercase tracking-wider text-[#020202]">
                                Catatan Pesanan (Opsional, Maks. 500 Karakter)
                            </label>
                            <textarea id="notes"
                                      name="notes"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Contoh: Es dipisah, gula sedikit, atau tambahan kantong kertas..."
                                      class="pop-input">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-xs font-bold text-[#b22e3b]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                    :disabled="submitting"
                                    :aria-busy="submitting.toString()"
                                    class="pop-btn-crimson w-full text-base py-4">
                                <span x-show="!submitting">Buat Pesanan Takeaway</span>
                                <span x-show="submitting" x-cloak>Memproses Pesanan...</span>
                            </button>
                        </div>
                    </form>
                </div>

                <aside class="lg:col-span-5 bg-[#e9bfd2] border-2 border-[#020202] p-6 space-y-5">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-[#020202]">
                            RINCIAN PESANAN
                        </p>
                        <h2 class="mt-1 text-2xl font-black uppercase tracking-tight text-[#020202]">
                            RINGKASAN ITEM
                        </h2>
                    </div>

                    <div class="divide-y-2 divide-[#020202]/15 border-y-2 border-[#020202]/20">
                        @foreach($cartItems as $item)
                            <div class="py-3.5 flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-black uppercase text-[#020202]">
                                            {{ $item['menu_name'] }}
                                        </p>
                                        @if(!$item['is_available'])
                                            <span class="px-2 py-0.5 bg-[#020202] text-[#fffaee] text-[10px] font-black uppercase tracking-wider">
                                                Tidak Tersedia
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-bold text-[#020202]/75">
                                        {{ $item['quantity'] }} x Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                    </p>
                                </div>
                                <span class="text-sm font-black text-[#020202] shrink-0">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <dl class="space-y-2 text-xs font-bold text-[#020202]">
                        <div class="flex justify-between">
                            <dt>Metode Pengambilan</dt>
                            <dd class="font-black uppercase text-[#020202]">Takeaway / Pick-up</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Estimasi Waktu Siap</dt>
                            <dd class="font-black">{{ $estimatedPickupText }}</dd>
                        </div>
                    </dl>

                    <div class="pt-3 border-t-2 border-[#020202] flex items-baseline justify-between">
                        <span class="text-sm font-black uppercase tracking-wider text-[#020202]">
                            Total Harga Server
                        </span>
                        <span class="text-2xl font-black text-[#b22e3b]">
                            Rp {{ number_format($total, 0, ',', '.') }}
                        </span>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</x-app-layout>
