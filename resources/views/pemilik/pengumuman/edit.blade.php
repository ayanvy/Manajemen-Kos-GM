<x-app-layout breadcrumb="Mengelola Pengumuman">
    <x-slot name="header">
        <h1 class="kos-page-title">Edit Pengumuman</h1>
        <p class="kos-page-sub">Diposting {{ $pengumuman->tgl_post_label }}.</p>
    </x-slot>

    @if ($errors->any())
        <x-alert type="error">Perubahan belum tersimpan. Periksa kembali isian yang ditandai di bawah.</x-alert>
    @endif

    <div class="kos-card" style="max-width: 44rem">
        @include('pemilik.pengumuman._form', [
            'action' => route('pemilik.pengumuman.update', $pengumuman),
            'method' => 'PATCH',
            'pengumuman' => $pengumuman,
            'submitLabel' => 'Simpan Perubahan',
        ])
    </div>
</x-app-layout>
