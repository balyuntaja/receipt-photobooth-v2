@extends('auth.layout')

@section('title', 'Masuk')

@section('content')
<div class="w-full max-w-sm">
    <div class="auth-animate delay-1">
        <h1 class="text-2xl font-semibold text-stone-800 tracking-tight mb-1">Masuk</h1>
        <p class="text-sm text-stone-500 mb-8">Masuk ke akun Anda</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800 auth-animate delay-2">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 auth-animate delay-2">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <div class="auth-animate delay-2">
            <label for="email" class="block text-sm font-medium text-stone-600 mb-1.5">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                class="auth-input w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-400 transition focus:border-stone-400">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-animate delay-3">
            <label for="password" class="block text-sm font-medium text-stone-600 mb-1.5">Password</label>
            <input type="password" name="password" id="password" required autocomplete="current-password"
                class="auth-input w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-400 transition focus:border-stone-400">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-animate delay-4 flex items-center">
            <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                class="rounded border-stone-300 text-stone-600 focus:ring-stone-400">
            <label for="remember" class="ml-2 text-sm text-stone-600">Ingat saya</label>
        </div>
        <div class="auth-animate delay-5 pt-1">
            <button type="submit" class="auth-btn w-full rounded-xl bg-stone-800 px-4 py-3.5 text-sm font-medium text-white hover:bg-stone-900">
                Masuk
            </button>
        </div>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500 auth-animate delay-6">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-medium text-stone-700 hover:text-stone-900 underline underline-offset-2">Daftar</a>
    </p>
</div>
@endsection
