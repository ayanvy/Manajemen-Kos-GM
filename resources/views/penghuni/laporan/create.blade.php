<x-app-layout breadcrumb="Laporan Kerusakan">
    <x-slot name="header">
        <h1 class="kos-page-title">Buat Laporan Kerusakan</h1>
        <p class="kos-page-sub">Laporan untuk kamar {{ $kamar->nomor_kamar }} akan dikirim ke pemilik kos.</p>
    </x-slot>

    @if ($errors->any())
        <x-alert type="error">Laporan belum terkirim. Periksa kembali isian yang ditandai di bawah.</x-alert>
    @endif

    <div class="kos-card" style="max-width: 44rem">
        @include('penghuni.laporan._form', [
            'action' => route('penghuni.laporan.store'),
            'method' => 'POST',
            'submitLabel' => 'Kirim Laporan',
        ])
    </div>
</x-app-layout>
