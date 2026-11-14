<x-app-layout breadcrumb="Riwayat Pembayaran">
    <x-slot name="header">
        <h1 class="kos-page-title">Riwayat Pembayaran</h1>
        <p class="kos-page-sub">Daftar pembayaran yang pernah dilakukan.</p>
    </x-slot>

    @if ($pembayaran->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="history" title="Belum ada riwayat pembayaran" text="Pembayaran yang Anda kirim akan tercatat di sini." />
        </div>
    @else
        <div class="kos-card" style="padding: 0; overflow: hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead>
                        <tr class="text-xs font-bold uppercase tracking-wide" style="color: var(--kos-muted); background: var(--kos-50)">
                            <th class="px-4 py-3">Tanggal bayar</th>
                            <th class="px-4 py-3">Periode</th>
                            <th class="px-4 py-3">Jumlah</th>
                            <th class="px-4 py-3">Metode</th>
                            <th class="px-4 py-3">Status verifikasi</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pembayaran as $item)
                            <tr class="border-t" style="border-color: var(--kos-line)">
                                <td class="px-4 py-3">{{ $item->tgl_bayar_label }}</td>
                                <td class="px-4 py-3 font-bold">{{ $item->tagihan->periode }}</td>
                                <td class="px-4 py-3">{{ $item->jumlah_rupiah }}</td>
                                <td class="px-4 py-3">{{ $item->metode_label }}</td>
                                <td class="px-4 py-3"><x-status-badge :tone="$item->status_tone">{{ $item->status_label }}</x-status-badge></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('penghuni.pembayaran.show', $item) }}" class="kos-btn kos-btn-outline">Detail</a>
                                        <a href="{{ route('penghuni.pembayaran.bukti', [$item, 'unduh' => 1]) }}" class="kos-btn kos-btn-outline">Unduh Bukti</a>
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
