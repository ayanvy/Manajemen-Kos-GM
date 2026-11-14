<x-app-layout breadcrumb="Mengelola Tagihan">
    <x-slot name="header">
        <h1 class="kos-page-title">Edit Tagihan {{ $tagihan->periode }}</h1>
        <p class="kos-page-sub">Ubah data tagihan untuk {{ $tagihan->nama_penghuni_tampil }}.</p>
    </x-slot>

    @if ($errors->any())
        <x-alert type="error">Perubahan belum tersimpan. Periksa kembali isian yang ditandai di bawah.</x-alert>
    @endif

    <div class="kos-card" style="max-width: 44rem">
        @include('pemilik.tagihan._form', [
            'action' => route('pemilik.tagihan.update', $tagihan),
            'method' => 'PATCH',
            'tagihan' => $tagihan,
            'submitLabel' => 'Simpan Perubahan',
        ])
    </div>
</x-app-layout>
