<x-app-layout breadcrumb="Mengelola Penghuni">
    <x-slot name="header">
        <h1 class="kos-page-title">Mengelola Penghuni</h1>
        <p class="kos-page-sub">Daftar akun penghuni yang terdaftar. Akun baru muncul otomatis setelah penghuni mendaftar.</p>
    </x-slot>

    @php $status = session('status'); @endphp

    @if ($status === 'penghuni-deleted')
        <x-alert>Akun penghuni berhasil dihapus.</x-alert>
    @endif

    @if ($penghuni->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="users" title="Belum ada penghuni" text="Akun penghuni akan tampil di sini setelah ada yang mendaftar." />
        </div>
    @else
        <div class="kos-card" style="padding: 0; overflow: hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead>
                        <tr class="text-xs font-bold uppercase tracking-wide" style="color: var(--kos-muted); background: var(--kos-50)">
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">No. HP</th>
                            <th class="px-4 py-3">Kamar</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($penghuni as $item)
                            <tr class="border-t" style="border-color: var(--kos-line)">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-user-avatar :user="$item" />
                                        <span class="font-bold">{{ $item->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ $item->email }}</td>
                                <td class="px-4 py-3">{{ $item->phone ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $item->kamar?->nomor_kamar ?? '-' }}</td>
                                <td class="px-4 py-3"><x-status-badge tone="success">Penghuni</x-status-badge></td>
                                <td class="px-4 py-3">
                                    <form method="POST"
                                          action="{{ route('pemilik.penghuni.destroy', $item) }}"
                                          onsubmit="return confirm(@js('Hapus akun ' . $item->name . '? Penghuni tidak bisa login lagi dan tindakan ini tidak bisa dibatalkan.'))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="kos-btn kos-btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
