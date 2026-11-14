<section>
    <header>
        <h2 class="kos-card-heading">Informasi Profil</h2>
        <p class="kos-card-sub mt-1">Perbarui nama, email, nomor HP, dan foto profil Anda.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5" novalidate>
        @csrf
        @method('patch')

        <div>
            <x-input-label for="profile_photo" value="Foto Profil" />

            <div class="mt-2 flex items-center gap-4">
                <div id="profile-photo-preview">
                    <x-user-avatar :user="$user" size="lg" />
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <label for="profile_photo" class="kos-btn kos-btn-soft">
                        <x-icon name="camera" class="h-4 w-4" />
                        Pilih Foto
                    </label>

                    <input
                        id="profile_photo"
                        name="profile_photo"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="sr-only"
                    />

                    @if ($user->profile_photo_path)
                        <button
                            type="button"
                            onclick="document.getElementById('delete-profile-photo-form').requestSubmit()"
                            class="kos-btn kos-btn-danger">
                            Hapus Foto
                        </button>
                    @endif
                </div>
            </div>

            <p class="kos-card-sub mt-2">Format JPG, PNG, atau WebP. Maksimal 2 MB.</p>
            <p id="profile-photo-error" class="kos-error" role="alert" hidden></p>
            <x-input-error :messages="$errors->get('profile_photo')" />
        </div>

        <div>
            <x-input-label for="name" value="Nama lengkap" />
            <x-text-input id="name" name="name" type="text" class="mt-1" :value="old('name', $user->name)" required :autofocus="! $errors->updatePassword->any() && (! $errors->has('email') || $errors->has('name'))" autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1" :value="old('email', $user->email)" required :autofocus="! $errors->updatePassword->any() && ! $errors->has('name') && $errors->has('email')" autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm">
                        Email Anda belum terverifikasi.

                        <button form="send-verification" class="kos-link text-sm">
                            Kirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium" style="color: var(--kos-700)">
                            Tautan verifikasi baru sudah dikirim ke email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone" value="Nomor HP" />
            <x-text-input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="13" class="mt-1" :value="old('phone', $user->phone)" placeholder="08xxxxxxxxxx" autocomplete="tel" />
            <p class="kos-card-sub mt-1">Hanya angka, 10 sampai 13 digit.</p>
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="role_display" value="Peran" />
            <x-text-input id="role_display" type="text" class="mt-1" :value="ucfirst($user->role)" disabled />
            <p class="kos-card-sub mt-1">Peran tidak dapat diubah dari halaman ini.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <x-primary-button>Simpan Perubahan</x-primary-button>
            <a href="{{ route('dashboard') }}" class="kos-btn kos-btn-outline">Batal</a>
        </div>
    </form>

    <form
        id="delete-profile-photo-form"
        method="POST"
        action="{{ route('profile.photo.destroy') }}"
        class="hidden"
    >
        @csrf
        @method('DELETE')
    </form>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('profile_photo');
        const preview = document.getElementById('profile-photo-preview');
        const error = document.getElementById('profile-photo-error');

        if (!input || !preview || !error) return;

        const originalPreview = preview.innerHTML;
        let previewUrl = null;

        function resetPreview(message) {
            error.textContent = message;
            error.hidden = false;
            input.value = '';

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }

            preview.innerHTML = originalPreview;
        }

        input.addEventListener('change', function () {
            error.textContent = '';
            error.hidden = true;

            const file = this.files[0];

            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                resetPreview('Ukuran foto maksimal 2 MB. Silakan pilih foto yang lebih kecil.');
                return;
            }

            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                resetPreview('Format foto harus JPG, JPEG, PNG, atau WebP.');
                return;
            }

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }

            previewUrl = URL.createObjectURL(file);

            const img = document.createElement('img');
            img.src = previewUrl;
            img.alt = 'Pratinjau foto profil';
            img.className = 'kos-avatar kos-avatar-lg';

            preview.replaceChildren(img);
        });
    });
</script>