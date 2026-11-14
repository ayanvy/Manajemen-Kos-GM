<x-app-layout breadcrumb="Mengelola Pengumuman">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="kos-page-title">Mengelola Pengumuman</h1>
                <p class="kos-page-sub">Tambah, ubah, dan hapus pengumuman.</p>
            </div>
            <a href="{{ route('pemilik.pengumuman.create') }}" class="kos-btn kos-btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Tambah Pengumuman
            </a>
        </div>
    </x-slot>

    @php $status = session('status'); @endphp

    @if ($status === 'pengumuman-created')
        <x-alert>Pengumuman berhasil ditambahkan.</x-alert>
    @elseif ($status === 'pengumuman-updated')
        <x-alert>Pengumuman berhasil diperbarui.</x-alert>
    @elseif ($status === 'pengumuman-deleted')
        <x-alert>Pengumuman berhasil dihapus.</x-alert>
    @endif

    @if ($pengumuman->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="megaphone" title="Belum ada pengumuman" text="Klik “Tambah Pengumuman” untuk membuat pengumuman pertama.">
            </x-empty-state>
        </div>
    @else
        <section class="grid gap-4 lg:grid-cols-2" aria-label="Daftar pengumuman">
            @foreach ($pengumuman as $item)
                <article class="kos-card">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 rounded-xl px-3 py-2 text-center" style="background: var(--kos-50); min-width: 3.25rem">
                            <p class="text-lg font-extrabold leading-none">{{ $item->tanggal_hari }}</p>
                            <p class="kos-row-sub mt-1">{{ $item->bulan_singkat }}</p>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="text-base font-extrabold">{{ $item->judul }}</h2>
                            <p class="mt-1 text-sm" style="color: var(--kos-muted)">{{ $item->ringkasan }}</p>
                            <p class="kos-row-sub mt-2">Diposting {{ $item->tgl_post_label }}</p>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <a href="{{ route('pemilik.pengumuman.show', $item) }}" class="kos-btn kos-btn-outline">Lihat Detail</a>
                                <a href="{{ route('pemilik.pengumuman.edit', $item) }}" class="kos-btn kos-btn-outline">Edit</a>

                                <form method="POST"
                                      action="{{ route('pemilik.pengumuman.destroy', $item) }}"
                                      onsubmit="return confirm(@js('Hapus pengumuman “'.$item->judul.'”? Tindakan ini tidak bisa dibatalkan.'))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="kos-btn kos-btn-danger">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</x-app-layout>
