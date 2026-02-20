<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Receipt Photobooth') }} – Photobooth dengan Cetak Receipt</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'DM Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="antialiased bg-stone-50 text-stone-900 min-h-screen">
    {{-- Header --}}
    <header class="border-b border-stone-200/80 bg-white/80 backdrop-blur-sm sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <span class="text-lg font-semibold text-stone-800">{{ config('app.name') }}</span>
            <nav class="flex items-center gap-4">
                <a href="{{ route('gallery') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900">Gallery</a>
                @auth
                    <a href="{{ url('/admin') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900">Dashboard</a>
                    <a href="{{ url('/admin') }}" class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-800">Masuk Admin</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900">Masuk</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-800">Daftar</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-16 sm:py-24">
        {{-- Hero --}}
        <section class="text-center mb-20">
            <h1 class="text-4xl sm:text-5xl font-bold text-stone-900 tracking-tight mb-4">
                Photobooth dengan Cetak Receipt
            </h1>
            <p class="text-lg sm:text-xl text-stone-600 max-w-2xl mx-auto mb-8">
                Solusi photobooth untuk event: ambil foto, pilih frame, cetak ke receipt printer thermal, 
                dan bagikan hasil via QR code. Mudah dipasang di kiosk atau tablet.
            </p>
            @auth
                <a href="{{ url('/admin/projects') }}" class="inline-flex items-center gap-2 rounded-xl bg-stone-900 px-6 py-3.5 text-base font-semibold text-white hover:bg-stone-800 transition">
                    Kelola Project
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-xl bg-stone-900 px-6 py-3.5 text-base font-semibold text-white hover:bg-stone-800 transition">
                    Mulai Sekarang
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            @endauth
        </section>

        {{-- Features --}}
        <section class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-20">
            <div class="rounded-2xl bg-white border border-stone-200/80 p-6 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-stone-900 mb-2">Capture & Frame</h2>
                <p class="text-stone-600 text-sm">Ambil foto dengan kamera, pilih template frame yang bisa dikustom per project. Support overlay slot foto sesuai layout frame.</p>
            </div>
            <div class="rounded-2xl bg-white border border-stone-200/80 p-6 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-stone-900 mb-2">Cetak Receipt Thermal</h2>
                <p class="text-stone-600 text-sm">Cetak langsung ke printer receipt thermal via WebUSB. Dithering otomatis untuk gambar berkualitas pada kertas thermal 58mm.</p>
            </div>
            <div class="rounded-2xl bg-white border border-stone-200/80 p-6 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-stone-900 mb-2">QR Code & Soft Copy</h2>
                <p class="text-stone-600 text-sm">Hasil foto bisa diunduh via QR code. Tamu scan QR dan dapat akses ke galeri foto sesi mereka.</p>
            </div>
        </section>

        {{-- Flow --}}
        <section class="rounded-2xl bg-stone-900 text-white p-8 sm:p-12 mb-20">
            <h2 class="text-2xl font-bold mb-6">Cara Kerja</h2>
            <ol class="space-y-6">
                <li class="flex gap-4">
                    <span class="flex-shrink-0 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-semibold">1</span>
                    <div>
                        <strong>Buat Project</strong> – Daftar, buat project photobooth, atur harga, pilih frame template.
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="flex-shrink-0 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-semibold">2</span>
                    <div>
                        <strong>Buka Booth</strong> – Buka URL booth di kiosk/tablet. Tamu bayar via QRIS, voucher, atau gratis.
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="flex-shrink-0 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-semibold">3</span>
                    <div>
                        <strong>Ambil Foto</strong> – Pilih frame, ambil foto, preview, lalu cetak ke receipt printer atau lanjut ke layar QR untuk unduh.
                    </div>
                </li>
            </ol>
        </section>

        {{-- CTA --}}
        <section class="text-center py-12 border-t border-stone-200/80">
            <p class="text-stone-600 mb-4">Siap mengadakan photobooth untuk event Anda?</p>
            @auth
                <a href="{{ url('/admin/projects') }}" class="text-stone-900 font-semibold hover:underline">Kelola project &raquo;</a>
            @else
                <a href="{{ route('register') }}" class="inline-block rounded-xl bg-stone-900 px-6 py-3 font-semibold text-white hover:bg-stone-800">Daftar Gratis</a>
            @endauth
        </section>
    </main>

    <footer class="border-t border-stone-200/80 py-6 text-center text-sm text-stone-500">
        &copy; {{ date('Y') }} {{ config('app.name') }}
    </footer>
</body>
</html>
