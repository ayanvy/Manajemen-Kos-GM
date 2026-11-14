@php
    // Password yang disimpan sementara oleh LoginRequest setelah login gagal
    try {
        $keptPassword = session('login_password')
            ? \Illuminate\Support\Facades\Crypt::decryptString(session('login_password'))
            : '';
    } catch (\Throwable) {
        $keptPassword = '';
    }
@endphp

<x-guest-layout>
    <h1 class="kos-card-title">Login KOS GM</h1>
    <p class="kos-card-desc">Masuk dengan akun Anda untuk mengelola kos atau melihat tagihan dan informasi kamar.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mt-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4" novalidate
          x-data="{ loading: false }" x-on:submit="loading = true" x-on:pageshow.window="loading = false">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" placeholder="nama@email.com" required :autofocus="$errors->has('email') || ! $errors->any()" autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Password" />
            <x-password-input id="password" name="password" class="mt-1" placeholder="Masukkan password" required :autofocus="! $errors->has('email') && $errors->has('password')" :value="$keptPassword" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <!-- Remember Me -->
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="kos-check" name="remember">
                <span class="ms-2 text-sm" style="color: var(--kos-muted)">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="kos-link text-sm" href="{{ route('password.request') }}">Lupa password?</a>
            @endif
        </div>

        <x-primary-button class="kos-btn-block" x-bind:disabled="loading" x-bind:aria-busy="loading ? 'true' : 'false'">
            <span class="inline-flex items-center gap-2" x-show="! loading">
                Masuk <x-icon name="arrow-right" class="h-4 w-4" />
            </span>
            <span class="inline-flex items-center gap-2" x-show="loading" x-cloak>
                <span class="kos-spinner" aria-hidden="true"></span> Memproses...
            </span>
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm" style="color: var(--kos-muted)">
        Belum punya akun?
        <a class="kos-link" href="{{ route('register') }}">Daftar</a>
    </p>
</x-guest-layout>