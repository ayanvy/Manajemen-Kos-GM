<x-app-layout breadcrumb="Mengelola Kamar">
    <x-slot name="header">
        <h1 class="kos-page-title">Edit Kamar {{ $kamar->nomor_kamar }}</h1>
        <p class="kos-page-sub">Ubah data kamar.</p>
    </x-slot>

    @if ($errors->any())
        <x-alert type="error">Perubahan belum tersimpan. Periksa kembali isian yang ditandai di bawah.</x-alert>
    @endif

    <div class="kos-card" style="max-width: 44rem">
        @include('pemilik.kamar._form', [
            'action' => route('pemilik.kamar.update', $kamar),
            'method' => 'PATCH',
            'kamar' => $kamar,
            'submitLabel' => 'Simpan Perubahan',
        ])
    </div>
</x-app-layout>
