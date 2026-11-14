<x-app-layout breadcrumb="Mengelola Peraturan Kos">
    <x-slot name="header">
        <h1 class="kos-page-title">Tambah Peraturan</h1>
        <p class="kos-page-sub">Peraturan baru akan ditaruh di urutan paling bawah.</p>
    </x-slot>

    @if ($errors->any())
        <x-alert type="error">Peraturan belum tersimpan. Periksa kembali isian yang ditandai di bawah.</x-alert>
    @endif

    <div class="kos-card" style="max-width: 44rem">
        @include('pemilik.peraturan._form', [
            'action' => route('pemilik.peraturan.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Peraturan',
        ])
    </div>
</x-app-layout>
