<x-app-layout breadcrumb="Mengelola Tagihan">
    <x-slot name="header">
        <h1 class="kos-page-title">Buat Tagihan</h1>
        <p class="kos-page-sub">Terbitkan tagihan baru untuk penghuni.</p>
    </x-slot>

    @if ($errors->any())
        <x-alert type="error">Tagihan belum tersimpan. Periksa kembali isian yang ditandai di bawah.</x-alert>
    @endif

    <div class="kos-card" style="max-width: 44rem">
        @include('pemilik.tagihan._form', [
            'action' => route('pemilik.tagihan.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Tagihan',
        ])
    </div>
</x-app-layout>
