<x-app-layout breadcrumb="Status Pembayaran">
    <x-slot name="header">
        <h1 class="kos-page-title">Status Pembayaran</h1>
        <p class="kos-page-sub">Status pembayaran dari proses verifikasi.</p>
    </x-slot>

    @if (session('status') === 'pembayaran-sent')
        <x-alert>Pembayaran berhasil dikirim dan menunggu verifikasi pemilik.</x-alert>
    @endif

    @if ($pembayaran->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="clock" title="Belum ada pembayaran" text="Pembayaran yang Anda kirim akan tampil di sini beserta status verifikasinya." />
        </div>
    @else
        <div class="space-y-4" style="max-width: 52rem">
            @foreach ($pembayaran as $item)
                @php
                    $aktif = 'background: var(--kos-700); color: #fff';
                    $tolak = 'background: var(--kos-danger-bg); color: var(--kos-danger)';
                    $tahap2 = $item->isDiterima() ? $aktif : ($item->isDitolak() ? $tolak : '');
                    $tahap3 = $item->isDiterima() ? $aktif : '';
                @endphp
                <article class="kos-card">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-2xl font-extrabold">{{ $item->jumlah_rupiah }}</h2>
                            <p class="kos-row-sub">{{ $item->metode_label }} &middot; {{ $item->tgl_bayar_label }}</p>
                        </div>
                        <x-status-badge :tone="$item->status_tone">{{ $item->status_label }}</x-status-badge>
                    </div>

                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="kos-card" style="box-shadow: none">
                            <dt class="kos-row-sub">Periode</dt>
                            <dd class="font-bold">{{ $item->tagihan->periode }}</dd>
                        </div>
                        <div class="kos-card" style="box-shadow: none">
                            <dt class="kos-row-sub">Status tagihan</dt>
                            <dd class="font-bold">{{ \App\Models\Tagihan::STATUS[$item->tagihan->status] ?? $item->tagihan->status }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span class="kos-badge" style="{{ $aktif }}">Dikirim</span>
                        <span aria-hidden="true">→</span>
                        <span class="kos-badge {{ $tahap2 ? '' : 'kos-badge-neutral' }}" style="{{ $tahap2 }}">{{ $item->isDitolak() ? 'Ditolak' : 'Diverifikasi' }}</span>
                        <span aria-hidden="true">→</span>
                        <span class="kos-badge {{ $tahap3 ? '' : 'kos-badge-neutral' }}" style="{{ $tahap3 }}">Lunas</span>
                    </div>

                    @if ($item->isDitolak())
                        <div class="mt-4 rounded-xl p-3 text-sm" style="background: var(--kos-danger-bg); color: var(--kos-danger)">
                            <p class="font-bold">Alasan penolakan</p>
                            <p class="mt-1">{{ $item->alasan_penolakan ?: 'Tidak ada alasan yang dicantumkan.' }}</p>
                        </div>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('penghuni.pembayaran.show', $item) }}" class="kos-btn kos-btn-outline">Detail</a>
                        @if ($item->isDitolak() && ! $item->tagihan->isLunas())
                            <a href="{{ route('penghuni.pembayaran.create', ['tagihan' => $item->tagihan_id]) }}" class="kos-btn kos-btn-primary">Kirim Ulang</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
