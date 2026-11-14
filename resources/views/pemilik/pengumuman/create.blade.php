<x-app-layout breadcrumb="Mengelola Pengumuman">
    <x-slot name="header">
        <h1 class="kos-page-title">Tambah Pengumuman</h1>
        <p class="kos-page-sub">Pengumuman akan tampil untuk penghuni kos Anda.</p>
    </x-slot>

    @if ($errors->any())
        <x-alert type="error">Pengumuman belum tersimpan. Periksa kembali isian yang ditandai di bawah.</x-alert>
    @endif

    <div class="kos-card" style="max-width: 44rem">
        @include('pemilik.pengumuman._form', [
            'action' => route('pemilik.pengumuman.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Pengumuman',
        ])
    </div>
</x-app-layout>
