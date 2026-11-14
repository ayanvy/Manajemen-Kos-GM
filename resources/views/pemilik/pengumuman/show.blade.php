<x-app-layout breadcrumb="Mengelola Pengumuman">
    <x-slot name="header">
        <h1 class="kos-page-title">Detail Pengumuman</h1>
        <p class="kos-page-sub">Diposting {{ $pengumuman->tgl_post_label }}.</p>
    </x-slot>

    <article class="kos-card" style="max-width: 44rem">
        <h2 class="text-xl font-extrabold">{{ $pengumuman->judul }}</h2>
        <p class="mt-3 whitespace-pre-line">{{ $pengumuman->isi }}</p>

        <div class="mt-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('pemilik.pengumuman.index') }}" class="kos-btn kos-btn-outline">Kembali</a>
            <a href="{{ route('pemilik.pengumuman.edit', $pengumuman) }}" class="kos-btn kos-btn-outline">Edit</a>

            <form method="POST"
                  action="{{ route('pemilik.pengumuman.destroy', $pengumuman) }}"
                  onsubmit="return confirm(@js('Hapus pengumuman “'.$pengumuman->judul.'”? Tindakan ini tidak bisa dibatalkan.'))">
                @csrf
                @method('DELETE')
                <button type="submit" class="kos-btn kos-btn-danger">Hapus</button>
            </form>
        </div>
    </article>
</x-app-layout>
