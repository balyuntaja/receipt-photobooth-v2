<div class="h-full w-full flex flex-col items-center justify-center p-6">
    <h1 class="text-2xl font-bold mb-4 text-gray-900">Printing...</h1>
    <div id="print-status" class="flex items-center gap-3 text-gray-600 mb-6">
        <svg class="animate-spin h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Mencetak photostrip...</span>
    </div>
    <div id="print-done" class="hidden text-center">
        <p class="text-green-400 mb-4">Print selesai!</p>
        <button type="button" id="btn-print-next" class="px-8 py-4 rounded-full bg-gray-900 text-white font-semibold hover:bg-gray-800">
            Lanjut
        </button>
    </div>
</div>
