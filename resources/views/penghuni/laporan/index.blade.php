<x-app-layout breadcrumb="Laporan Kerusakan">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="kos-page-title">Laporan Kerusakan</h1>
                <p class="kos-page-sub">Membuat laporan kerusakan pada kamar.</p>
            </div>
            @if ($kamar)
                <a href="{{ route('penghuni.laporan.create') }}" class="kos-btn kos-btn-primary">
                    <x-icon name="plus" class="h-4 w-4" />
                    Buat Laporan
                </a>
            @endif
        </div>
    </x-slot>

    @php $status = session('status'); @endphp
    @if ($status === 'laporan-created')
        <x-alert>Laporan kerusakan berhasil dikirim ke pemilik.</x-alert>
    @elseif ($status === 'laporan-deleted')
        <x-alert>Laporan kerusakan berhasil dihapus.</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    @if (! $kamar && $laporan->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="door" title="Anda belum menempati kamar" text="Laporan kerusakan bisa dibuat setelah pemilik mengonfirmasi kamar Anda." />
        </div>
    @elseif ($laporan->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="wrench" title="Belum ada laporan" text='Klik "Buat Laporan" kalau ada kerusakan di kamar Anda.' />
        </div>
    @else
        <div class="kos-card" style="padding: 0; overflow: hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead>
                        <tr class="text-xs font-bold uppercase tracking-wide" style="color: var(--kos-muted); background: var(--kos-50)">
                            <th class="px-4 py-3">Tanggal</th>
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
                                <td class="px-4 py-3">{{ $item->nomor_kamar_tampil }}</td>
                                <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($item->deskripsi, 70) }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :tone="$item->status_tone">{{ $item->status_label }}</x-status-badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('penghuni.laporan.show', $item) }}" class="kos-btn kos-btn-outline">Detail</a>

                                        @if ($item->bisaDiubahPenghuni())
                                            <a href="{{ route('penghuni.laporan.edit', $item) }}" class="kos-btn kos-btn-outline">Edit</a>

                                            <form method="POST" action="{{ route('penghuni.laporan.destroy', $item) }}"
                                                  onsubmit="return confirm(@js('Hapus laporan kerusakan kamar ' . $item->nomor_kamar_tampil . '? Tindakan ini tidak bisa dibatalkan.'))">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="kos-btn kos-btn-danger">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
