<x-app-layout breadcrumb="Mengelola Tagihan">
    <x-slot name="header">
        <h1 class="kos-page-title">Detail Tagihan</h1>
        <p class="kos-page-sub">Tagihan periode {{ $tagihan->periode }}.</p>
    </x-slot>

    <article class="kos-card" style="max-width: 44rem">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="kos-row-sub">Jumlah tagihan</p>
                <h2 class="text-2xl font-extrabold">{{ $tagihan->jumlah_rupiah }}</h2>
            </div>
            <x-status-badge :tone="$tagihan->status_tone">{{ $tagihan->status_label }}</x-status-badge>
        </div>

        @if ($tagihan->isArsip())
            <p class="kos-row-sub mt-3">
                Arsip &middot; hanya-baca
                @if ($tagihan->penghuni_id === null)
                    &middot; akun penghuni sudah dihapus
                @endif
                @if ($tagihan->pemilik_id === null)
                    &middot; diterbitkan oleh {{ $tagihan->nama_pemilik_tampil }}
                @endif
            </p>
        @endif

        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Penghuni</dt>
                <dd class="font-bold">{{ $tagihan->nama_penghuni_tampil }}</dd>
            </div>
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Kamar</dt>
                <dd class="font-bold">{{ $tagihan->penghuni?->kamar?->nomor_kamar ?? '-' }}</dd>
            </div>
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Periode</dt>
                <dd class="font-bold">{{ $tagihan->periode }}</dd>
            </div>
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Jatuh tempo</dt>
                <dd class="font-bold">{{ $tagihan->jatuh_tempo_label }}</dd>
            </div>
        </dl>

        <h3 class="mt-6 text-lg font-extrabold">Riwayat pembayaran</h3>

        @if ($tagihan->pembayaran->isEmpty())
            <p class="kos-row-sub mt-2">Belum ada pembayaran untuk tagihan ini.</p>
        @else
            <div class="mt-3 space-y-2">
                @foreach ($tagihan->pembayaran as $item)
                    <div class="kos-card flex flex-wrap items-center justify-between gap-3" style="box-shadow: none">
                        <div>
                            <p class="font-bold">{{ $item->jumlah_rupiah }} &middot; {{ $item->metode_label }}</p>
                            <p class="kos-row-sub">{{ $item->tgl_bayar_label }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-status-badge :tone="$item->status_tone">{{ $item->status_label }}</x-status-badge>
                            @if ($tagihan->isMilikPemilik(auth()->id()))
                                <a href="{{ route('pemilik.transaksi.show', $item) }}" class="kos-btn kos-btn-outline">Detail</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-5">
            <a href="{{ route('pemilik.tagihan.index') }}" class="kos-btn kos-btn-outline">Kembali</a>
        </div>
    </article>
</x-app-layout>
