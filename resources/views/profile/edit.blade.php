<x-app-layout breadcrumb="Profil Saya">
    <x-slot name="header">
        <h1 class="kos-page-title">Profil Saya</h1>
        <p class="kos-page-sub">Kelola data akun, foto profil, dan password Anda.</p>
    </x-slot>

    @php $status = session('status'); @endphp

    @if ($status === 'profile-updated')
        <x-alert>Perubahan profil berhasil disimpan.</x-alert>
    @elseif ($status === 'profile-photo-deleted')
        <x-alert>Foto profil berhasil dihapus.</x-alert>
    @elseif ($status === 'password-updated')
        <x-alert>Password berhasil diperbarui.</x-alert>
    @endif

    @if ($errors->any() || $errors->updatePassword->any())
        <x-alert type="error">Perubahan belum tersimpan. Periksa kembali isian yang ditandai di bawah.</x-alert>
    @endif

    <div class="kos-grid-profile">
        {{-- Ringkasan akun --}}
        <aside class="kos-card text-center">
            <x-user-avatar :user="$user" size="lg" class="mx-auto" />
            <h2 class="mt-4 text-lg font-extrabold">{{ $user->name }}</h2>
            <p class="text-sm" style="color: var(--kos-muted); word-break: break-word">{{ $user->email }}</p>
            <div class="mt-3">
                <x-status-badge tone="success">{{ $user->role === \App\Models\User::ROLE_PEMILIK ? 'Pemilik' : 'Penghuni' }}</x-status-badge>
            </div>

            <dl class="kos-dl mt-5 text-left">
                <div class="kos-dl-row">
                    <dt>Nomor HP</dt>
                    <dd>{{ $user->phone ?: '-' }}</dd>
                </div>
                <div class="kos-dl-row">
                    <dt>Bergabung</dt>
                    <dd>{{ $user->created_at?->format('d/m/Y') ?? '-' }}</dd>
                </div>
            </dl>
        </aside>

        <div class="space-y-5">
            <div class="kos-card">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="kos-card">
                @include('profile.partials.update-password-form')
            </div>

            <div class="kos-card" style="border-color: #f3d3d3">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
