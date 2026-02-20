{{--
  Done screen: QR code for soft files, New Session button.
--}}
<div class="kiosk-screen flex h-full w-full flex-col items-center justify-center p-6">
    <h1 class="kiosk-screen-title mb-2 text-xl font-semibold text-gray-800">Here comes your soft files</h1>
    <p class="mb-6 max-w-md text-center text-gray-600 text-sm">Scan QR code untuk mengunduh foto Anda</p>
    <div id="qr-container" class="mb-6 rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm">
        {{-- QR code injected by JS --}}
    </div>
    <button type="button" id="btn-reset" class="rounded-xl border border-gray-300 bg-white px-8 py-4 font-medium text-gray-800 shadow-sm transition-colors hover:bg-gray-50">
        New Session
    </button>
</div>
