<x-app-layout breadcrumb="Pengumuman">
    <x-slot name="header">
        <h1 class="kos-page-title">Pengumuman</h1>
        <p class="kos-page-sub">Melihat pengumuman kos.</p>
    </x-slot>

    @if ($pengumuman->isEmpty())
        <div class="kos-card">
            @if ($punyaKamar)
                <x-empty-state icon="megaphone" title="Belum ada pengumuman" text="Pengumuman dari pemilik kos akan tampil di sini." />
            @else
                <x-empty-state icon="megaphone" title="Belum ada pengumuman" text="Pengumuman akan tampil setelah pemilik mengonfirmasi kamar Anda." />
            @endif
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

                            <div class="mt-3">
                                <a href="{{ route('penghuni.pengumuman.show', $item) }}" class="kos-btn kos-btn-outline">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</x-app-layout>
