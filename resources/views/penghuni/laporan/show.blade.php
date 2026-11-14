<x-app-layout breadcrumb="Laporan Kerusakan">
    <x-slot name="header">
        <h1 class="kos-page-title">Detail Laporan</h1>
        <p class="kos-page-sub">Laporan kerusakan kamar {{ $laporan->nomor_kamar_tampil }}, {{ $laporan->tgl_lapor_label }}.</p>
    </x-slot>

    @php $status = session('status'); @endphp
    @if ($status === 'laporan-updated')
        <x-alert>Laporan kerusakan berhasil diperbarui.</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <article class="kos-card" style="max-width: 44rem">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="kos-row-sub">Deskripsi kerusakan</p>
                <p class="mt-1 whitespace-pre-line">{{ $laporan->deskripsi }}</p>
            </div>
            <x-status-badge :tone="$laporan->status_tone">{{ $laporan->status_label }}</x-status-badge>
        </div>

        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Kamar</dt>
                <dd class="font-bold">{{ $laporan->nomor_kamar_tampil }}</dd>
            </div>
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Tanggal lapor</dt>
                <dd class="font-bold">{{ $laporan->tgl_lapor_label }}</dd>
            </div>
            @if ($laporan->tgl_selesai_label)
                <div class="kos-card" style="box-shadow: none">
                    <dt class="kos-row-sub">Tanggal selesai</dt>
                    <dd class="font-bold">{{ $laporan->tgl_selesai_label }}</dd>
                </div>
            @endif
        </dl>

        @if ($laporan->foto)
            <div class="mt-4">
                <p class="kos-row-sub">Foto bukti</p>
                <a href="{{ route('penghuni.laporan.foto', $laporan) }}" target="_blank" rel="noopener">
                    <img src="{{ route('penghuni.laporan.foto', $laporan) }}" alt="Foto kerusakan"
                         class="mt-2 rounded-xl" style="max-width: 100%; max-height: 24rem">
                </a>
            </div>
        @endif

        <x-laporan-stepper :laporan="$laporan" class="mt-4" />

        @if ($laporan->catatan_pemilik)
            <div class="mt-4 rounded-xl p-3 text-sm" style="background: var(--kos-50)">
                <p class="font-bold">Catatan pemilik</p>
                <p class="mt-1 whitespace-pre-line">{{ $laporan->catatan_pemilik }}</p>
            </div>
        @endif

        <div class="mt-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('penghuni.laporan.status') }}" class="kos-btn kos-btn-outline">Kembali</a>

            @if ($laporan->bisaDiubahPenghuni())
                <a href="{{ route('penghuni.laporan.edit', $laporan) }}" class="kos-btn kos-btn-outline">Edit</a>

                <form method="POST" action="{{ route('penghuni.laporan.destroy', $laporan) }}"
                      onsubmit="return confirm(@js('Hapus laporan kerusakan kamar ' . $laporan->nomor_kamar_tampil . '? Tindakan ini tidak bisa dibatalkan.'))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="kos-btn kos-btn-danger">Hapus</button>
                </form>
            @endif
        </div>
    </article>
</x-app-layout>
