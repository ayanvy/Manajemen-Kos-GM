<x-app-layout breadcrumb="Pengumuman">
    <x-slot name="header">
        <h1 class="kos-page-title">Detail Pengumuman</h1>
        <p class="kos-page-sub">Diposting {{ $pengumuman->tgl_post_label }}.</p>
    </x-slot>

    <article class="kos-card" style="max-width: 44rem">
        <h2 class="text-xl font-extrabold">{{ $pengumuman->judul }}</h2>
        <p class="mt-3 whitespace-pre-line">{{ $pengumuman->isi }}</p>

        <div class="mt-5">
            <a href="{{ route('penghuni.pengumuman.index') }}" class="kos-btn kos-btn-outline">Kembali</a>
        </div>
    </article>
</x-app-layout>
