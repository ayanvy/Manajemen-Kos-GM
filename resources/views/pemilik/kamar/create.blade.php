<x-app-layout breadcrumb="Mengelola Kamar">
    <x-slot name="header">
        <h1 class="kos-page-title">Tambah Kamar</h1>
        <p class="kos-page-sub">Isi data kamar baru.</p>
    </x-slot>

    @if ($errors->any())
        <x-alert type="error">Kamar belum tersimpan. Periksa kembali isian yang ditandai di bawah.</x-alert>
    @endif

    <div class="kos-card" style="max-width: 44rem">
        @include('pemilik.kamar._form', [
            'action' => route('pemilik.kamar.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Kamar',
        ])
    </div>
</x-app-layout>
