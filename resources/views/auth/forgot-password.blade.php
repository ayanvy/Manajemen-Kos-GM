<x-guest-layout>
    <h1 class="kos-card-title">Lupa password?</h1>
    <p class="kos-card-desc">Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat password baru.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mt-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4" novalidate>
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" placeholder="nama@email.com" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="kos-btn-block">Kirim tautan reset password</x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm" style="color: var(--kos-muted)">
        Ingat password Anda?
        <a class="kos-link" href="{{ route('login') }}">Kembali ke login</a>
    </p>
</x-guest-layout>