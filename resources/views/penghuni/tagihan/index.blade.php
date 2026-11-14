<x-app-layout breadcrumb="Tagihan">
    <x-slot name="header">
        <h1 class="kos-page-title">Tagihan</h1>
        <p class="kos-page-sub">Melihat daftar tagihan dan rincian tagihan.</p>
    </x-slot>

    @if ($tagihan->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="receipt" title="Belum ada tagihan" text="Tagihan kos Anda akan muncul di sini setelah diterbitkan oleh pemilik." />
        </div>
    @else
        <div class="kos-card" style="padding: 0; overflow: hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-left text-sm">
                    <thead>
                        <tr class="text-xs font-bold uppercase tracking-wide" style="color: var(--kos-muted); background: var(--kos-50)">
                            <th class="px-4 py-3">Periode</th>
                            <th class="px-4 py-3">Jumlah tagihan</th>
                            <th class="px-4 py-3">Jatuh tempo</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tagihan as $item)
                            <tr class="border-t" style="border-color: var(--kos-line)">
                                <td class="px-4 py-3 font-bold">{{ $item->periode }}</td>
                                <td class="px-4 py-3">{{ $item->jumlah_rupiah }}</td>
                                <td class="px-4 py-3">{{ $item->jatuh_tempo_label }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :tone="$item->status_tone">{{ $item->status_label }}</x-status-badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('penghuni.tagihan.show', $item) }}" class="kos-btn kos-btn-outline">Lihat Rincian</a>

                                        @unless ($item->isLunas())
                                            @if (Route::has('penghuni.pembayaran.create'))
                                                <a href="{{ route('penghuni.pembayaran.create', ['tagihan' => $item->id]) }}" class="kos-btn kos-btn-primary">Bayar</a>
                                            @else
                                                {{-- Fitur pembayaran belum dibuat: tombol tampil tapi nonaktif. --}}
                                                <button type="button" class="kos-btn kos-btn-primary" disabled
                                                        style="opacity: .55; cursor: not-allowed"
                                                        title="Fitur pembayaran belum tersedia">Bayar</button>
                                            @endif
                                        @endunless
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
