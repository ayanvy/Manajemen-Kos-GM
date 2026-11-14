<x-app-layout breadcrumb="Mengelola Tagihan">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="kos-page-title">Mengelola Tagihan</h1>
                <p class="kos-page-sub">Tambah, ubah, dan hapus data tagihan.</p>
            </div>
            <a href="{{ route('pemilik.tagihan.create') }}" class="kos-btn kos-btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Buat Tagihan
            </a>
        </div>
    </x-slot>

    @php $status = session('status'); @endphp

    @if ($status === 'tagihan-created')
        <x-alert>Tagihan baru berhasil dibuat.</x-alert>
    @elseif ($status === 'tagihan-updated')
        <x-alert>Tagihan berhasil diperbarui.</x-alert>
    @elseif ($status === 'tagihan-deleted')
        <x-alert>Tagihan berhasil dihapus.</x-alert>
    @endif

    @if ($tagihan->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="receipt" title="Belum ada tagihan" text='Klik "Buat Tagihan" untuk membuat tagihan pertama bagi penghuni.' />
        </div>
    @else
        <div class="kos-card" style="padding: 0; overflow: hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead>
                        <tr class="text-xs font-bold uppercase tracking-wide" style="color: var(--kos-muted); background: var(--kos-50)">
                            <th class="px-4 py-3">Penghuni</th>
                            <th class="px-4 py-3">Kamar</th>
                            <th class="px-4 py-3">Periode</th>
                            <th class="px-4 py-3">Jumlah</th>
                            <th class="px-4 py-3">Jatuh tempo</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tagihan as $item)
                            <tr class="border-t" style="border-color: var(--kos-line)">
                                <td class="px-4 py-3">
                                    <span class="font-bold">{{ $item->nama_penghuni_tampil }}</span>
                                    @if ($item->isArsip())
                                        <p class="kos-row-sub">
                                            Arsip &middot; hanya-baca
                                            @if ($item->pemilik_id === null)
                                                &middot; diterbitkan oleh {{ $item->nama_pemilik_tampil }}
                                            @endif
                                            @if ($item->penghuni_id === null)
                                                &middot; akun penghuni sudah dihapus
                                            @endif
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $item->penghuni?->kamar?->nomor_kamar ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $item->periode }}</td>
                                <td class="px-4 py-3">{{ $item->jumlah_rupiah }}</td>
                                <td class="px-4 py-3">{{ $item->jatuh_tempo_label }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :tone="$item->status_tone">{{ $item->status_label }}</x-status-badge>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($item->isArsip())
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('pemilik.tagihan.show', $item) }}" class="kos-btn kos-btn-outline">Detail</a>
                                            <span class="kos-row-sub">Arsip &middot; hanya-baca</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('pemilik.tagihan.show', $item) }}" class="kos-btn kos-btn-outline">Detail</a>
                                            <a href="{{ route('pemilik.tagihan.edit', $item) }}" class="kos-btn kos-btn-outline">Edit</a>

                                            <form method="POST"
                                                  action="{{ route('pemilik.tagihan.destroy', $item) }}"
                                                  onsubmit="return confirm(@js('Hapus tagihan ' . $item->periode . ' milik ' . $item->nama_penghuni_tampil . '? Tindakan ini tidak bisa dibatalkan.'))">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="kos-btn kos-btn-danger">Hapus</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
