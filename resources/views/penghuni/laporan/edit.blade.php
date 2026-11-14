<x-app-layout breadcrumb="Laporan Kerusakan">
    <x-slot name="header">
        <h1 class="kos-page-title">Edit Laporan Kerusakan</h1>
        <p class="kos-page-sub">Laporan hanya bisa diubah selama belum diproses pemilik.</p>
    </x-slot>

    @if ($errors->any())
        <x-alert type="error">Laporan belum tersimpan. Periksa kembali isian yang ditandai di bawah.</x-alert>
    @endif

    <div class="kos-card" style="max-width: 44rem">
        @include('penghuni.laporan._form', [
            'action' => route('penghuni.laporan.update', $laporan),
            'method' => 'PUT',
            'submitLabel' => 'Simpan Perubahan',
            'laporan' => $laporan,
        ])
    </div>
</x-app-layout>
