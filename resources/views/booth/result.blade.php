<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>{{ $project->name }} – Your Photos</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#000000">

    @vite(['resources/css/booth.css', 'resources/js/booth/result.js'])
</head>

<body class="m-0 p-0 min-h-screen bg-white text-gray-900 font-sans"
    data-session-id="{{ $session->id }}"
    data-media="{{ json_encode($media->map(fn ($m) => ['id' => $m->id, 'url' => asset('storage/' . $m->file_path), 'type' => $m->type->value])->values()->all()) }}">

    <div class="container mx-auto max-w-4xl px-4 py-8">
        <h1 class="text-3xl font-light mb-2">Here comes your <strong>soft files</strong></h1>
        <p class="text-gray-600 mb-6">Terima kasih telah memilih {{ $project->name }}. Download foto Anda sebelum masa berlaku habis.</p>

        <div class="flex flex-wrap gap-3 mb-8">
            <button type="button" id="btn-download-all" class="px-6 py-3 rounded-full bg-gray-900 text-white font-semibold hover:bg-gray-800">
                Download All
            </button>
        </div>

        <div id="result-loading" class="flex items-center justify-center py-12 text-gray-500">
            Loading photos...
        </div>
        <div id="result-empty" class="hidden py-12 text-center text-gray-500">
            No photos found for this session.
        </div>
        <div id="result-grid" class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Photos injected by JS --}}
        </div>
    </div>

    {{-- Popup detail foto --}}
    <div id="result-photo-popup" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/80 p-4" aria-hidden="true">
        <div class="relative flex max-h-[90vh] max-w-[90vw] items-center justify-center">
            <img id="result-popup-image" src="" alt="" class="max-h-[90vh] max-w-full object-contain rounded-lg shadow-2xl" />
            <div class="absolute right-3 top-3 z-10 flex gap-2">
                <a id="result-popup-download" href="#" download class="flex h-10 w-10 items-center justify-center rounded-full bg-white/95 text-gray-800 shadow-lg hover:bg-white" title="Download">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                </a>
                <button type="button" id="result-popup-close" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/95 text-gray-800 shadow-lg hover:bg-white" title="Tutup" aria-label="Tutup">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
    </div>

</body>

</html>
