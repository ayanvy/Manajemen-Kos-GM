<x-app-layout breadcrumb="Riwayat Transaksi">
    <x-slot name="header">
        <h1 class="kos-page-title">Riwayat Transaksi</h1>
        <p class="kos-page-sub">Melihat seluruh transaksi dan memfilter berdasarkan periode atau penghuni.</p>
    </x-slot>

    <form method="GET" action="{{ route('pemilik.transaksi.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label for="periode" class="kos-label">Periode</label>
            <select id="periode" name="periode" class="kos-input" onchange="this.form.submit()">
                <option value="">Semua periode</option>
                @foreach ($periodeOptions as $opsi)
                    <option value="{{ $opsi }}" @selected($periode === $opsi)>{{ $opsi }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="penghuni" class="kos-label">Penghuni</label>
            <select id="penghuni" name="penghuni" class="kos-input" onchange="this.form.submit()">
                <option value="">Semua penghuni</option>
                @foreach ($penghuniOptions as $opsi)
                    <option value="{{ $opsi->penghuni_id }}" @selected($penghuniId === (string) $opsi->penghuni_id)>{{ $opsi->nama_penghuni }}</option>
                @endforeach
            </select>
        </div>
        @if ($periode !== '' || $penghuniId !== '')
            <a href="{{ route('pemilik.transaksi.index') }}" class="kos-btn kos-btn-outline">Reset</a>
        @endif
        <noscript><button type="submit" class="kos-btn kos-btn-primary">Terapkan</button></noscript>
    </form>

    @if ($transaksi->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="history" title="Belum ada transaksi" text="Transaksi pembayaran penghuni akan tercatat di sini." />
        </div>
    @else
        <div class="kos-card" style="padding: 0; overflow: hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead>
                        <tr class="text-xs font-bold uppercase tracking-wide" style="color: var(--kos-muted); background: var(--kos-50)">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Penghuni</th>
                            <th class="px-4 py-3">Periode</th>
                            <th class="px-4 py-3">Jumlah</th>
                            <th class="px-4 py-3">Metode</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksi as $item)
                            <tr class="border-t" style="border-color: var(--kos-line)">
                                <td class="px-4 py-3">{{ $item->tgl_bayar_label }}</td>
                                <td class="px-4 py-3 font-bold">{{ $item->tagihan->nama_penghuni_tampil }}</td>
                                <td class="px-4 py-3">{{ $item->tagihan->periode }}</td>
                                <td class="px-4 py-3">{{ $item->jumlah_rupiah }}</td>
                                <td class="px-4 py-3">{{ $item->metode_label }}</td>
                                <td class="px-4 py-3"><x-status-badge :tone="$item->status_tone">{{ $item->status_label }}</x-status-badge></td>
                                <td class="px-4 py-3"><a href="{{ route('pemilik.transaksi.show', $item) }}" class="kos-btn kos-btn-outline">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
