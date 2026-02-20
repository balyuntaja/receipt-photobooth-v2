<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>{{ $project->name }} – Booth</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#000000">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- CSP untuk Midtrans Snap popup (termasuk snap-assets CDN) --}}
    <meta http-equiv="Content-Security-Policy" content="script-src 'self' 'unsafe-inline' 'unsafe-eval' https://app.sandbox.midtrans.com https://app.midtrans.com https://*.midtrans.com https://snap-assets.*.cdn.gtflabs.io blob: http://localhost:* http://127.0.0.1:*; frame-src 'self' https://app.sandbox.midtrans.com https://app.midtrans.com https://*.midtrans.com blob:; style-src 'self' 'unsafe-inline' https://app.sandbox.midtrans.com https://app.midtrans.com https://*.midtrans.com https://snap-assets.*.cdn.gtflabs.io https: http://localhost:* http://127.0.0.1:*; connect-src 'self' https://app.sandbox.midtrans.com https://app.midtrans.com https://*.midtrans.com https://snap-assets.*.cdn.gtflabs.io wss: ws: https: http://localhost:* http://127.0.0.1:* blob:; img-src 'self' data: https: blob:; font-src 'self' data: https:">

    @vite(['resources/css/booth.css', 'resources/js/booth/kiosk.js'])
</head>

<body class="m-0 p-0 h-screen font-sans overflow-hidden"
    data-session-id="{{ $session->id }}"
    data-project-id="{{ $project->id }}"
    data-initial-state="{{ $initialState ?? 'IDLE' }}"
    data-csrf="{{ csrf_token() }}"
    data-price-per-session="{{ $pricePerSession ?? 0 }}"
    data-create-payment-url="{{ route('booth.session.create-payment', $session) }}"
    data-apply-voucher-url="{{ route('booth.session.apply-voucher', $session) }}"
    data-confirm-free-url="{{ route('booth.session.confirm-free', $session) }}"
    data-midtrans-client-key="{{ config('midtrans.client_key') }}"
    data-midtrans-is-production="{{ config('midtrans.is_production') ? '1' : '0' }}"
    data-save-frame-url="{{ route('booth.session.frame', $session) }}"
    data-update-session-url="{{ route('booth.session.update', $session) }}"
    data-save-media-url="{{ route('booth.session.media', $session) }}"
    data-result-url="{{ url(route('booth.result', $session)) }}"
    data-frames="{{ json_encode($framesForKiosk ?? $frames->map(fn ($f) => ['id' => $f->id, 'name' => $f->name, 'preview' => asset('storage/' . $f->preview_image), 'frame_file' => asset('storage/' . $f->frame_file), 'photo_slots' => $f->photo_slots ?? [], 'template_width' => 1920, 'template_height' => 1080])->values()) }}"
    data-setting="{{ json_encode(['copies' => $setting->copies ?? 1, 'max_retakes' => $setting->max_retakes ?? 3, 'countdown_seconds' => $setting->countdown_seconds ?? 3]) }}"
    data-copy-price-options="{{ json_encode($copyPriceOptions ?? [1 => $pricePerSession ?? 0]) }}">

    {{-- Welcome Screen (IDLE state) - rendered with components from database --}}
    @include('booth.screens.welcome', ['welcomeComponents' => $welcomeComponents])

    <div id="screen-payment" class="booth-screen booth-screen-white hidden" data-state="PAYMENT">
        @include('booth.screens.payment')
    </div>

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

    <div id="screen-result" class="booth-screen booth-screen-white hidden" data-state="RESULT">
        @include('booth.screens.result')
    </div>

    <div id="screen-qr" class="booth-screen booth-screen-white hidden" data-state="DONE">
        @include('booth.screens.qr')
    </div>

    {{-- Camera Settings Modal --}}
    @include('booth.components.camera-settings-modal')

</body>

</html>
