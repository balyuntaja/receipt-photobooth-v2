@extends('auth.layout')

@section('title', 'Daftar')

@section('content')
<div class="w-full max-w-sm">
    <div class="auth-animate delay-1">
        <h1 class="text-2xl font-semibold text-stone-800 tracking-tight mb-1">Daftar</h1>
        <p class="text-sm text-stone-500 mb-8">Buat akun baru</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 auth-animate delay-2">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf
        <div class="auth-animate delay-2">
            <label for="name" class="block text-sm font-medium text-stone-600 mb-1.5">Nama</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="auth-input w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-400 transition focus:border-stone-400">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-animate delay-3">
            <label for="email" class="block text-sm font-medium text-stone-600 mb-1.5">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email"
                class="auth-input w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-400 transition focus:border-stone-400">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-animate delay-4">
            <label for="password" class="block text-sm font-medium text-stone-600 mb-1.5">Password</label>
            <input type="password" name="password" id="password" required autocomplete="new-password"
                class="auth-input w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-400 transition focus:border-stone-400">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-animate delay-5">
            <label for="password_confirmation" class="block text-sm font-medium text-stone-600 mb-1.5">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                class="auth-input w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-400 transition focus:border-stone-400">
        </div>
        <div class="auth-animate delay-6">
            <label for="referral_code" class="block text-sm font-medium text-stone-600 mb-1.5">Kode Referral <span class="text-red-500">*</span></label>
            <input type="text" name="referral_code" id="referral_code" value="{{ old('referral_code') }}" required placeholder=""
                class="auth-input w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-400 transition focus:border-stone-400 uppercase">
            @error('referral_code')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-stone-400">Kode referral wajib diisi untuk mendaftar.</p>
        </div>
        <div class="auth-animate delay-7 pt-1">
            <button type="submit" class="auth-btn w-full rounded-xl bg-stone-800 px-4 py-3.5 text-sm font-medium text-white hover:bg-stone-900">
                Daftar
            </button>
        </div>
    </form>

    <p class="mt-8 text-center text-sm text-stone-500 auth-animate delay-7">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-medium text-stone-700 hover:text-stone-900 underline underline-offset-2">Masuk</a>
    </p>
</div>
@endsection
