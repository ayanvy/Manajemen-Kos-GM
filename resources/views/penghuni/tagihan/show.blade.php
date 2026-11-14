<x-app-layout breadcrumb="Tagihan">
    <x-slot name="header">
        <h1 class="kos-page-title">Rincian Tagihan</h1>
        <p class="kos-page-sub">Tagihan kos periode {{ $tagihan->periode }}.</p>
    </x-slot>

    <article class="kos-card" style="max-width: 44rem">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="kos-row-sub">Jumlah tagihan</p>
                <h2 class="text-2xl font-extrabold">{{ $tagihan->jumlah_rupiah }}</h2>
            </div>
            <x-status-badge :tone="$tagihan->status_tone">{{ $tagihan->status_label }}</x-status-badge>
        </div>

        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Periode</dt>
                <dd class="font-bold">{{ $tagihan->periode }}</dd>
            </div>
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Jatuh tempo</dt>
                <dd class="font-bold">{{ $tagihan->jatuh_tempo_label }}</dd>
            </div>
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Kamar</dt>
                <dd class="font-bold">{{ $tagihan->penghuni->kamar?->nomor_kamar ?? '-' }}</dd>
            </div>
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Status tagihan</dt>
                <dd class="font-bold">{{ \App\Models\Tagihan::STATUS[$tagihan->status] ?? $tagihan->status }}</dd>
            </div>
        </dl>

        <div class="mt-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('penghuni.tagihan.index') }}" class="kos-btn kos-btn-outline">Kembali</a>

            @unless ($tagihan->isLunas())
                @if (Route::has('penghuni.pembayaran.create'))
                    <a href="{{ route('penghuni.pembayaran.create', ['tagihan' => $tagihan->id]) }}" class="kos-btn kos-btn-primary">Bayar</a>
                @else
                    <button type="button" class="kos-btn kos-btn-primary" disabled
                            style="opacity: .55; cursor: not-allowed"
                            title="Fitur pembayaran belum tersedia">Bayar</button>
                @endif
            @endunless
        </div>
    </article>
</x-app-layout>
