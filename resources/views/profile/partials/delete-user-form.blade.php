<section class="space-y-5">
    <header>
        <h2 class="kos-card-heading" style="color: var(--kos-danger)">Hapus Akun</h2>
        <p class="kos-card-sub mt-1">
            Setelah akun dihapus, seluruh data akun akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Hapus Akun</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="kos-card-heading">Yakin ingin menghapus akun?</h2>

            <p class="kos-card-sub mt-2">
                Akun dan seluruh datanya akan dihapus permanen. Masukkan password Anda untuk mengonfirmasi.
            </p>

            <div class="mt-5">
                <x-input-label for="delete_account_password" value="Password" class="sr-only" />

                <x-password-input
                    id="delete_account_password"
                    name="password"
                    placeholder="Password"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>

                <x-danger-button>Hapus Akun</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
