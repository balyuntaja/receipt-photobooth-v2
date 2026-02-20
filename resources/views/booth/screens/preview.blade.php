{{--
  Preview screen: choose one photo, see merged preview, mirror option, continue to print.
--}}
<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col">
    @include('booth.components.screen-header', [
        'backId' => 'btn-preview-back',
        'title' => 'Pilih Satu Foto Terbaik',
    ])

    <div class="kiosk-screen-body flex-1 overflow-y-auto p-4 sm:p-6">
        <div class="mx-auto grid w-full max-w-4xl grid-cols-1 gap-6 md:grid-cols-2">
            <section>
                <h2 class="mb-4 text-lg font-semibold text-gray-700">Foto Anda</h2>
                <div id="preview-photo-grid" class="grid grid-cols-2 gap-4">
                    {{-- Photos injected by JS --}}
                </div>
            </section>
            <section>
                <h2 class="mb-4 text-lg font-semibold text-gray-700">Preview</h2>
                <div id="preview-merged" class="flex w-full bg-gray-200 max-h-[400px] items-center justify-center overflow-hidden rounded-2xl bg-white border border-gray-200/80 shadow-sm">
                    <p class="text-gray-500 text-sm">Pilih foto untuk melihat preview</p>
                </div>
                <div class="mt-4 flex flex-col gap-3">
                    <label class="flex cursor-pointer items-center gap-2 text-gray-600 text-sm">
                        <input type="checkbox" id="preview-mirror" class="rounded border-gray-300 text-gray-700 focus:ring-gray-400" />
                        <span>Mirror image</span>
                    </label>
                    <button type="button" id="btn-preview-print" disabled class="w-full cursor-not-allowed rounded-xl bg-gray-200 px-6 py-3 font-medium text-gray-500 transition-colors">
                        Lanjut ke Print
                    </button>
                </div>
            </section>
        </div>
    </div>
</div>
