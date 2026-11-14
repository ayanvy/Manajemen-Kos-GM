@php
    // Password yang disimpan sementara oleh PasswordController setelah gagal
    $ambilTersimpan = function (string $key) {
        try {
            return session($key)
                ? \Illuminate\Support\Facades\Crypt::decryptString(session($key))
                : '';
        } catch (\Throwable) {
            return '';
        }
    };

    $keptCurrent = $ambilTersimpan('update_current_password');
    $keptPassword = $ambilTersimpan('update_password');
    $keptConfirmation = $ambilTersimpan('update_password_confirmation');
@endphp

<section>
    <header>
        <h2 class="kos-card-heading">Ubah Password</h2>
        <p class="kos-card-sub mt-1">Gunakan password yang panjang dan sulit ditebak agar akun tetap aman.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5" novalidate>
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Password saat ini" />
            <x-password-input id="update_password_current_password" name="current_password" class="mt-1" :autofocus="$errors->updatePassword->has('current_password')" :value="$keptCurrent" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Password baru" />
            <x-password-input id="update_password_password" name="password" class="mt-1" :autofocus="! $errors->updatePassword->has('current_password') && $errors->updatePassword->has('password')" :value="$keptPassword" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Konfirmasi password baru" />
            <x-password-input id="update_password_password_confirmation" name="password_confirmation" class="mt-1" :autofocus="! $errors->updatePassword->has('current_password') && ! $errors->updatePassword->has('password') && $errors->updatePassword->has('password_confirmation')" :value="$keptConfirmation" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div>
            <x-primary-button>Simpan Password</x-primary-button>
        </div>
    </form>
</section>