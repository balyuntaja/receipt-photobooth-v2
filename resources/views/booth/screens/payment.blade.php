{{--
  Payment screen: pilih QRIS atau Voucher.
  Setelah welcome, sebelum pilih frame.
--}}
<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col">
    @include('booth.components.screen-header', [
        'backId' => 'btn-payment-back',
        'backVisible' => true,
        'title' => 'Pembayaran',
        'subtitle' => 'Pilih metode pembayaran',
    ])

    <div class="kiosk-screen-body flex flex-1 flex-col items-center justify-center p-6 overflow-y-auto">
        {{-- Pilih jumlah print --}}
        <div id="payment-copy-section" class="mx-auto mb-6 w-full max-w-2xl">
            <p class="mb-2 text-sm font-medium text-gray-700">Print berapa kali?</p>
            <div id="payment-copy-options" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                {{-- Diisi oleh JS dari data-copy-price-options --}}
            </div>
            <p id="payment-selected-price" class="mt-2 text-sm text-gray-600 hidden"></p>
        </div>

        <div id="payment-cards" class="mx-auto grid w-full max-w-2xl grid-cols-1 gap-5 sm:grid-cols-2">
            {{-- QRIS --}}
            <button
                type="button"
                id="btn-payment-qris"
                class="payment-card flex flex-col items-center justify-center gap-3 rounded-2xl border border-gray-200/80 bg-white p-8 text-left transition-all hover:border-gray-300 hover:bg-white focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 focus:ring-offset-[#f5f5f7]"
            >
                <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-green-50 text-green-600">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </span>
                <span class="text-lg font-semibold text-gray-900">QRIS</span>
                <span class="text-center text-sm text-gray-500">Bayar dengan QRIS via Midtrans</span>
            </button>

            {{-- Voucher --}}
            <div
                class="payment-card flex flex-col rounded-2xl border border-gray-200/80 bg-white p-6 transition-all focus-within:ring-2 focus-within:ring-gray-300 focus-within:ring-offset-2 focus-within:ring-offset-[#f5f5f7]"
            >
                <div class="mb-3 flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </span>
                    <span class="text-lg font-semibold text-gray-900">Pakai Voucher</span>
                </div>
                <p class="mb-3 text-sm text-gray-500">Masukkan kode voucher dari dashboard</p>
                <div class="flex gap-2">
                    <input
                        type="text"
                        id="payment-voucher-input"
                        placeholder="Kode voucher"
                        class="flex-1 rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm focus:border-gray-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gray-400"
                        autocomplete="off"
                    />
                    <button
                        type="button"
                        id="btn-payment-voucher-apply"
                        class="shrink-0 rounded-xl bg-gray-800 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-gray-700"
                    >
                        Pakai
                    </button>
                </div>
                <p id="payment-voucher-error" class="mt-2 hidden text-sm text-red-600"></p>
            </div>
        </div>

        {{-- Gratis (hanya tampil jika price_per_session == 0) --}}
        <div id="payment-free-wrap" class="mt-6 hidden">
            <button
                type="button"
                id="btn-payment-free"
                class="rounded-2xl border border-gray-200/80 bg-white px-8 py-4 text-lg font-medium text-gray-800 shadow-sm transition-all hover:bg-white hover:shadow"
            >
                Lanjut (Gratis)
            </button>
        </div>
    </div>
</div>
