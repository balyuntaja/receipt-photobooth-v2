{{--
  Masukkan kode promo: minimal UI selaras dengan login/register (stone, rounded-xl). Tanpa Pindai QR.
--}}
<div class="kiosk-screen flex h-full w-full flex-col items-center justify-center bg-stone-100 p-6">
    <div class="w-full max-w-sm">
        <h1 class="text-2xl font-semibold text-stone-800 tracking-tight mb-1">Masukkan kode promo</h1>
        <p class="text-sm text-stone-500 mb-6">Masukkan kode voucher dari dashboard</p>

        <label for="promo-code-input" class="sr-only">Kode promo</label>
        <input
            type="text"
            id="promo-code-input"
            placeholder="Masukkan kode promo"
            autocomplete="off"
            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-400 transition focus:border-stone-400 focus:outline-none focus:ring-2 focus:ring-stone-400/20"
        />
        <p id="promo-code-error" class="mt-2 hidden text-sm text-red-600"></p>

        <div class="mt-6 flex gap-3">
            <button type="button" id="btn-promo-cancel" class="flex-1 rounded-xl border border-stone-200 bg-white py-3 text-sm font-medium text-stone-600 transition-colors hover:bg-stone-50 hover:text-stone-800">
                Batal
            </button>
            <button type="button" id="btn-promo-apply" class="flex-1 rounded-xl bg-stone-800 px-4 py-3.5 text-sm font-medium text-white transition-colors hover:bg-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-400 focus:ring-offset-2 focus:ring-offset-stone-100 disabled:opacity-60 disabled:pointer-events-none" data-default-label="Terapkan" data-loading-label="Memproses...">
                Terapkan
            </button>
        </div>
    </div>
</div>
