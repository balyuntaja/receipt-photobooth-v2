{{--
  Modal "Kredit Tidak Tersedia" – tampil saat user belum memasukkan token langganan.
  Variables: $langgananUrl, $dompetUrl (untuk tombol Aktifkan Langganan & Isi Ulang Kredit).
--}}
<div
    id="credit-modal"
    class="booth-modal-backdrop hidden"
    aria-hidden="true"
    role="dialog"
    aria-labelledby="credit-modal-title"
    aria-modal="true"
>
    <div class="booth-modal max-w-md">
        <h2 id="credit-modal-title" class="mb-3 text-xl font-bold text-gray-900">Kredit Tidak Tersedia</h2>
        <p class="mb-6 text-gray-700">
            Anda tidak memiliki cukup kredit untuk menggunakan aplikasi ini. Harap isi ulang kredit Anda atau berlangganan untuk melanjutkan.
        </p>

        {{-- Token input (untuk user yang sudah punya token) --}}
        <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
            <label for="credit-modal-token" class="mb-2 block text-sm font-medium text-gray-700">Sudah punya token? Masukkan di sini</label>
            <div class="flex gap-2">
                <input
                    type="text"
                    id="credit-modal-token"
                    placeholder="Masukkan token langganan"
                    class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    autocomplete="off"
                />
                <button type="button" id="credit-modal-validate" class="shrink-0 rounded-lg bg-gray-700 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-800">
                    Validasi
                </button>
            </div>
            <p id="credit-modal-token-error" class="mt-2 hidden text-sm text-red-600"></p>
        </div>

        <div class="flex flex-col gap-3">
            <a
                href="{{ $langgananUrl ?? url('/admin') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-3 font-semibold text-white transition-colors hover:bg-blue-700"
            >
                Aktifkan Langganan
            </a>
            <a
                href="{{ $dompetUrl ?? url('/admin/dompet') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-gray-700 px-4 py-3 font-semibold text-white transition-colors hover:bg-gray-800"
            >
                Isi Ulang Kredit
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            </a>
            <button
                type="button"
                id="credit-modal-close"
                class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 font-semibold text-gray-900 transition-colors hover:bg-gray-50"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
