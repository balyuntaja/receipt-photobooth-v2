{{--
  Print screen: printing status, then "Lanjut" when done.
--}}
<div class="kiosk-screen flex h-full w-full flex-col items-center justify-center p-6">
    <h1 class="kiosk-screen-title mb-4 text-xl font-semibold text-gray-800">Printing...</h1>
    <div id="print-status" class="mb-6 flex items-center gap-3 text-gray-600 text-sm">
        <svg class="h-7 w-7 animate-spin text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Mencetak photostrip...</span>
    </div>
    <div id="print-done" class="hidden text-center">
        <p class="mb-4 text-green-600 font-medium">Print selesai!</p>
        <button type="button" id="btn-print-next" class="rounded-xl bg-gray-800 px-8 py-4 font-medium text-white transition-colors hover:bg-gray-700">
            Lanjut
        </button>
    </div>
</div>
