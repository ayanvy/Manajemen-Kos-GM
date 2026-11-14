@php
    // Password yang disimpan sementara oleh RegisteredUserController setelah gagal
    $ambilTersimpan = function (string $key) {
        try {
            return session($key)
                ? \Illuminate\Support\Facades\Crypt::decryptString(session($key))
                : '';
        } catch (\Throwable) {
            return '';
        }
    };

    $keptPassword = $ambilTersimpan('register_password');
    $keptConfirmation = $ambilTersimpan('register_password_confirmation');
@endphp

<x-guest-layout>
    <h1 class="kos-card-title">Daftar KOS GM</h1>
    <p class="kos-card-desc">Buat akun penghuni untuk melihat kamar, tagihan, dan pengumuman kos.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4" novalidate
          x-data="{ loading: false }" x-on:submit="loading = true" x-on:pageshow.window="loading = false">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" value="Nama lengkap" />
            <x-text-input id="name" class="mt-1" type="text" name="name" :value="old('name')" placeholder="Nama sesuai identitas" required :autofocus="$errors->has('name') || ! $errors->any()" autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" placeholder="nama@email.com" required :autofocus="! $errors->has('name') && $errors->has('email')" autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Password" />
            <x-password-input id="password" name="password" class="mt-1" placeholder="Buat password" required :autofocus="! $errors->has('name') && ! $errors->has('email') && $errors->has('password')" :value="$keptPassword" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi password" />
            <x-password-input id="password_confirmation" name="password_confirmation" class="mt-1" placeholder="Ulangi password" required :autofocus="! $errors->has('name') && ! $errors->has('email') && ! $errors->has('password') && $errors->has('password_confirmation')" :value="$keptConfirmation" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="kos-btn-block" x-bind:disabled="loading" x-bind:aria-busy="loading ? 'true' : 'false'">
            <span class="inline-flex items-center gap-2" x-show="! loading">
                Daftar <x-icon name="arrow-right" class="h-4 w-4" />
            </span>
            <span class="inline-flex items-center gap-2" x-show="loading" x-cloak>
                <span class="kos-spinner" aria-hidden="true"></span> Memproses...
            </span>
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm" style="color: var(--kos-muted)">
        Sudah punya akun?
        <a class="kos-link" href="{{ route('login') }}">Login</a>
    </p>
</x-guest-layout>