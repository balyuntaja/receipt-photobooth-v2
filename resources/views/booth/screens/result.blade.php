{{--
  Result screen: show QR code to access photos, upload status.
--}}
<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col" data-state="RESULT">
    @include('booth.components.screen-header', [
        'title' => 'Foto Siap!',
    ])

    <div class="kiosk-screen-body flex-1 overflow-y-auto p-4 sm:p-6">
        <div class="mx-auto flex max-w-2xl flex-col items-center gap-6 text-center">
            {{-- Upload Status --}}
            <div id="result-upload-status" class="w-full">
                <div class="flex items-center justify-center gap-3 text-gray-600">
                    <svg class="h-6 w-6 animate-spin text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium">Mengupload foto...</span>
                </div>
            </div>

            {{-- QR Code Section --}}
            <div id="result-qr-section" class="hidden w-full">
                <h2 class="mb-4 text-lg font-semibold text-gray-700">Scan QR Code untuk Mengunduh Foto</h2>
                <div class="flex flex-col items-center gap-4 rounded-2xl bg-white p-6 shadow-lg">
                    <div id="result-qr-code" class="flex items-center justify-center rounded-lg bg-white p-4">
                        {{-- QR code akan di-generate oleh JavaScript --}}
                    </div>
                    <p class="text-sm text-gray-600">Scan QR code di atas untuk mengakses foto Anda</p>
                    <div class="mt-2 text-xs text-gray-500">
                        <p>Atau kunjungi:</p>
                        <p id="result-url" class="mt-1 break-all font-mono text-xs text-blue-600"></p>
                    </div>
                </div>
            </div>

            {{-- Error Message --}}
            <div id="result-error" class="hidden w-full rounded-lg bg-red-50 border border-red-200 p-4 text-red-700 text-sm">
                <p>Gagal mengupload foto. Silakan coba lagi.</p>
            </div>

            {{-- Action Buttons --}}
            <div id="result-actions" class="hidden w-full flex flex-col sm:flex-row gap-3">
                <button type="button" id="btn-result-home" class="flex-1 rounded-xl bg-gray-200 px-6 py-3 font-medium text-gray-700 transition-colors hover:bg-gray-300">
                    Kembali ke Home
                </button>
            </div>
        </div>
    </div>
</div>
