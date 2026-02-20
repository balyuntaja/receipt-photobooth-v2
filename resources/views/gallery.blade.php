<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gallery – {{ config('app.name') }}</title>
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
            <a href="{{ url('/') }}" class="text-lg font-semibold text-stone-800 hover:text-stone-600">{{ config('app.name') }}</a>
            <nav class="flex items-center gap-4">
                <a href="{{ route('gallery') }}" class="text-sm font-medium text-stone-900">Gallery</a>
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

    <main class="max-w-2xl mx-auto px-4 sm:px-6 py-16 sm:py-24">
        <section class="text-center mb-8">
            <h1 class="text-3xl font-bold text-stone-900 mb-2">Gallery Foto</h1>
            <p class="text-stone-600">
                Masukkan Session ID dari receipt atau QR code untuk melihat dan mengunduh foto Anda.
            </p>
        </section>

        <form action="{{ route('gallery') }}" method="get" class="space-y-4">
            <div>
                <label for="session" class="block text-sm font-medium text-stone-700 mb-1">Session ID</label>
                <input
                    type="text"
                    name="session"
                    id="session"
                    value="{{ old('session', $sessionId ?? '') }}"
                    placeholder="contoh: abc123"
                    autocomplete="off"
                    autofocus
                    class="w-full rounded-xl border border-stone-300 px-4 py-3 text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:ring-1 focus:ring-stone-500"
                />
            </div>
            @if (!empty($error))
                <p class="text-sm text-red-600">{{ $error }}</p>
            @endif
            <button
                type="submit"
                class="w-full rounded-xl bg-stone-900 px-4 py-3 text-base font-semibold text-white hover:bg-stone-800 transition"
            >
                Cari Foto
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-stone-500">
            Session ID dapat Anda temukan pada receipt cetak atau di halaman QR setelah selesai foto.
        </p>
    </main>
</body>
</html>
