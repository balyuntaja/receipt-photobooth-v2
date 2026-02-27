{{--
  Tinjau Pesanan: minimal UI selaras dengan login/register (stone, rounded-xl).
--}}
<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col bg-stone-100">
    @include('booth.components.screen-header', [
        'backId' => 'btn-review-back',
        'backVisible' => true,
        'title' => 'Tinjau Pesanan Anda',
    ])

    <div class="kiosk-screen-body flex flex-1 flex-col items-center justify-center p-6 overflow-y-auto">
        <div class="w-full max-w-sm">
            {{-- Card pesanan --}}
            <div class="rounded-xl border border-stone-200 bg-white p-5">
                <div class="flex items-center justify-between gap-4 border-b border-stone-100 pb-4">
                    <div class="min-w-0 shrink">
                        <p class="font-medium text-stone-800">Cetak</p>
                        <p class="text-sm text-stone-500">Pilih jumlah cetakan</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <span id="review-copy-promo" class="hidden text-xs text-stone-500 sm:inline sm:min-w-24">Dapat 2 strip!</span>
                        <div class="flex items-center rounded-xl border border-stone-200 bg-white">
                            <button type="button" id="review-copy-minus" class="flex h-9 w-9 items-center justify-center rounded-l-xl text-stone-600 transition-colors hover:bg-stone-100 disabled:opacity-50 disabled:pointer-events-none" aria-label="Kurangi">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            </button>
                            <span id="review-copy-value" class="min-w-8 text-center text-sm font-medium text-stone-800">1</span>
                            <button type="button" id="review-copy-plus" class="flex h-9 w-9 items-center justify-center rounded-r-xl text-stone-600 transition-colors hover:bg-stone-100" aria-label="Tambah">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        <span id="review-copy-price" class="min-w-22 text-right text-sm font-semibold text-stone-800">0</span>
                    </div>
                </div>
                <div class="mt-4 space-y-1 pt-4 border-t border-stone-100">
                    <div class="flex justify-between text-sm text-stone-600">
                        <span>Subtotal</span>
                        <span id="review-subtotal">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm font-semibold text-stone-800">
                        <span>Total</span>
                        <span id="review-total">Rp 0</span>
                    </div>
                </div>
            </div>

            <div id="review-payment-error" class="mt-3 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"></div>

            <button type="button" id="btn-review-promo" class="mt-4 w-full rounded-xl border border-stone-200 bg-white py-3 text-sm font-medium text-stone-600 transition-colors hover:bg-stone-50 hover:text-stone-800">
                Punya kode promo?
            </button>

            <button type="button" id="btn-review-to-payment" class="mt-4 w-full rounded-xl bg-stone-800 px-4 py-3.5 text-sm font-medium text-white transition-colors hover:bg-stone-900 focus:outline-none focus:ring-2 focus:ring-stone-400 focus:ring-offset-2 focus:ring-offset-stone-100 disabled:cursor-not-allowed disabled:opacity-70" data-default-label="Lanjutkan ke Pembayaran" data-loading-label="Memproses...">
                Lanjutkan ke Pembayaran
            </button>
        </div>
    </div>
</div>
