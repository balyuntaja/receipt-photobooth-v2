<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>{{ $project->name }} – Booth</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#000000">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/booth.css', 'resources/js/booth/kiosk.js'])
</head>

<body class="m-0 p-0 h-screen font-sans overflow-hidden"
    data-session-id="{{ $session->id }}"
    data-project-id="{{ $project->id }}"
    data-initial-state="{{ $initialState ?? 'IDLE' }}"
    data-csrf="{{ csrf_token() }}"
    data-save-frame-url="{{ route('booth.session.frame', $session) }}"
    data-update-session-url="{{ route('booth.session.update', $session) }}"
    data-save-media-url="{{ route('booth.session.media', $session) }}"
    data-result-url="{{ url(route('booth.result', $session)) }}"
    data-frames="{{ json_encode($frames->map(fn ($f) => ['id' => $f->id, 'name' => $f->name, 'preview' => asset('storage/' . $f->preview_image), 'frame_file' => asset('storage/' . $f->frame_file)])->values()) }}"
    data-setting="{{ json_encode(['copies' => $setting->copies ?? 1, 'max_retakes' => $setting->max_retakes ?? 3]) }}">

    {{-- Welcome Screen (IDLE state) - rendered with components from database --}}
    @include('booth.screens.welcome', ['welcomeComponents' => $welcomeComponents])

    <div id="screen-frame" class="booth-screen booth-screen-white hidden" data-state="FRAME">
        @include('booth.screens.frame')
    </div>

    <div id="screen-capture" class="booth-screen booth-screen-white hidden" data-state="CAPTURE">
        @include('booth.screens.capture')
    </div>

    <div id="screen-preview" class="booth-screen booth-screen-white hidden" data-state="PREVIEW">
        @include('booth.screens.preview')
    </div>

    <div id="screen-print" class="booth-screen booth-screen-white hidden" data-state="PRINT">
        @include('booth.screens.print')
    </div>

    <div id="screen-qr" class="booth-screen booth-screen-white hidden" data-state="DONE">
        @include('booth.screens.qr')
    </div>

    {{-- Camera Settings Modal --}}
    @include('booth.components.camera-settings-modal')

</body>

</html>
