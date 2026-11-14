<x-app-layout breadcrumb="Mengelola Peraturan Kos">
    <x-slot name="header">
        <h1 class="kos-page-title">Edit Peraturan</h1>
        <p class="kos-page-sub">Terakhir diperbarui {{ $peraturan->diperbarui_label }}.</p>
    </x-slot>

    @if ($errors->any())
        <x-alert type="error">Perubahan belum tersimpan. Periksa kembali isian yang ditandai di bawah.</x-alert>
    @endif

    <div class="kos-card" style="max-width: 44rem">
        @include('pemilik.peraturan._form', [
            'action' => route('pemilik.peraturan.update', $peraturan),
            'method' => 'PATCH',
            'peraturan' => $peraturan,
            'submitLabel' => 'Simpan Perubahan',
        ])
    </div>
</x-app-layout>
