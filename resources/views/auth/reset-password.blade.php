@php
    // Password yang disimpan sementara oleh NewPasswordController setelah gagal
    $ambilTersimpan = function (string $key) {
        try {
            return session($key)
                ? \Illuminate\Support\Facades\Crypt::decryptString(session($key))
                : '';
        } catch (\Throwable) {
            return '';
        }
    };

    $keptPassword = $ambilTersimpan('reset_password');
    $keptConfirmation = $ambilTersimpan('reset_password_confirmation');
@endphp

<x-guest-layout>
    <h1 class="kos-card-title">Buat password baru</h1>
    <p class="kos-card-desc">Pilih password baru untuk akun Anda.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4" novalidate>
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email', $request->email)" required :autofocus="$errors->has('email') || ! $errors->any()" autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Password baru" />
            <x-password-input id="password" name="password" class="mt-1" required :autofocus="! $errors->has('email') && $errors->has('password')" :value="$keptPassword" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi password baru" />
            <x-password-input id="password_confirmation" name="password_confirmation" class="mt-1" required :autofocus="! $errors->has('email') && ! $errors->has('password') && $errors->has('password_confirmation')" :value="$keptConfirmation" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="kos-btn-block">Simpan password baru</x-primary-button>
    </form>
</x-guest-layout>