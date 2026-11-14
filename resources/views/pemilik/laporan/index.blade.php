<x-app-layout breadcrumb="Mengelola Laporan">
    <x-slot name="header">
        <h1 class="kos-page-title">Mengelola Laporan Kerusakan</h1>
        <p class="kos-page-sub">Melihat dan memperbarui status laporan.</p>
    </x-slot>

    @if (session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    @if ($laporan->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="wrench" title="Belum ada laporan" text="Laporan kerusakan dari penghuni akan muncul di sini." />
        </div>
    @else
        <div class="kos-card" style="padding: 0; overflow: hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead>
                        <tr class="text-xs font-bold uppercase tracking-wide" style="color: var(--kos-muted); background: var(--kos-50)">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Penghuni</th>
                            <th class="px-4 py-3">Kamar</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($laporan as $item)
                            <tr class="border-t" style="border-color: var(--kos-line)">
                                <td class="px-4 py-3">{{ $item->tgl_lapor_label }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-bold">{{ $item->nama_penghuni_tampil }}</span>
                                    @if ($item->isArsip())
                                        <p class="kos-row-sub">
                                            Arsip &middot; hanya-baca
                                            @if ($item->penghuni_id === null)
                                                &middot; akun penghuni sudah dihapus
                                            @endif
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $item->nomor_kamar_tampil }}</td>
                                <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($item->deskripsi, 70) }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :tone="$item->status_tone">{{ $item->status_label }}</x-status-badge>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('pemilik.laporan.show', $item) }}" class="kos-btn kos-btn-outline">
                                        {{ ($item->isArsip() || $item->isSelesai()) ? 'Detail' : 'Kelola' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
