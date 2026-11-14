<x-app-layout breadcrumb="Status Laporan">
    <x-slot name="header">
        <h1 class="kos-page-title">Status Laporan</h1>
        <p class="kos-page-sub">Melihat perkembangan laporan kerusakan.</p>
    </x-slot>

    @if ($laporan->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="clipboard" title="Belum ada laporan" text="Laporan kerusakan yang Anda buat akan tampil di sini beserta perkembangannya." />
        </div>
    @else
        <div class="space-y-4" style="max-width: 52rem">
            @foreach ($laporan as $item)
                <article class="kos-card">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-extrabold">Laporan #{{ $item->id }}</h2>
                            <p class="kos-row-sub">Kamar {{ $item->nomor_kamar_tampil }} &middot; {{ $item->tgl_lapor_label }}</p>
                        </div>
                        <x-status-badge :tone="$item->status_tone">{{ $item->status_label }}</x-status-badge>
                    </div>

                    <p class="mt-3">{{ \Illuminate\Support\Str::limit($item->deskripsi, 140) }}</p>

                    <x-laporan-stepper :laporan="$item" class="mt-4" />

                    <div class="mt-4">
                        <a href="{{ route('penghuni.laporan.show', $item) }}" class="kos-btn kos-btn-outline">Lihat Detail &amp; Progres</a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
