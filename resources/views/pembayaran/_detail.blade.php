{{-- Rincian satu pembayaran. Variabel: $pembayaran, $buktiRoute (nama route bukti), $kembaliRoute, $tampilkanPenghuni --}}
<article class="kos-card" style="max-width: 44rem">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="kos-row-sub">Jumlah bayar</p>
            <h2 class="text-2xl font-extrabold">{{ $pembayaran->jumlah_rupiah }}</h2>
        </div>
        <x-status-badge :tone="$pembayaran->status_tone">{{ $pembayaran->status_label }}</x-status-badge>
    </div>

    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
        @if ($tampilkanPenghuni ?? false)
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Penghuni</dt>
                <dd class="font-bold">{{ $pembayaran->tagihan->nama_penghuni_tampil }}</dd>
            </div>
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Kamar</dt>
                <dd class="font-bold">{{ $pembayaran->tagihan->penghuni?->kamar?->nomor_kamar ?? '-' }}</dd>
            </div>
        @endif
        <div class="kos-card" style="box-shadow: none">
            <dt class="kos-row-sub">Periode</dt>
            <dd class="font-bold">{{ $pembayaran->tagihan->periode }}</dd>
        </div>
        <div class="kos-card" style="box-shadow: none">
            <dt class="kos-row-sub">Metode</dt>
            <dd class="font-bold">{{ $pembayaran->metode_label }}</dd>
        </div>
        <div class="kos-card" style="box-shadow: none">
            <dt class="kos-row-sub">Tanggal bayar</dt>
            <dd class="font-bold">{{ $pembayaran->tgl_bayar_label }}</dd>
        </div>
        <div class="kos-card" style="box-shadow: none">
            <dt class="kos-row-sub">Diverifikasi</dt>
            <dd class="font-bold">
                @if ($pembayaran->tgl_verifikasi)
                    {{ \App\Models\Tagihan::formatTanggal($pembayaran->tgl_verifikasi) }}
                    @if ($pembayaran->verifikator) &middot; {{ $pembayaran->verifikator->name }} @endif
                @else
                    Belum diverifikasi
                @endif
            </dd>
        </div>
    </dl>

    @if ($pembayaran->isDitolak() && $pembayaran->alasan_penolakan)
        <div class="mt-4 rounded-xl p-3 text-sm" style="background: var(--kos-danger-bg); color: var(--kos-danger)">
            <p class="font-bold">Alasan penolakan</p>
            <p class="mt-1">{{ $pembayaran->alasan_penolakan }}</p>
        </div>
    @endif

    <div class="mt-5 flex flex-wrap items-center gap-2">
        <a href="{{ route($kembaliRoute) }}" class="kos-btn kos-btn-outline">Kembali</a>
        <a href="{{ route($buktiRoute, $pembayaran) }}" target="_blank" rel="noopener" class="kos-btn kos-btn-outline">Lihat Bukti</a>
        <a href="{{ route($buktiRoute, [$pembayaran, 'unduh' => 1]) }}" class="kos-btn kos-btn-outline">Unduh Bukti</a>
    </div>
</article>
