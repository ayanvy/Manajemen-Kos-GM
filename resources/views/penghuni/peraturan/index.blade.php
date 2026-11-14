<x-app-layout breadcrumb="Peraturan Kos">
    <x-slot name="header">
        <h1 class="kos-page-title">Peraturan Kos</h1>
        <p class="kos-page-sub">Melihat peraturan kos.</p>
    </x-slot>

    @if ($peraturan->isEmpty())
        <div class="kos-card">
            @if ($punyaKamar)
                <x-empty-state icon="list" title="Belum ada peraturan" text="Peraturan dari pemilik kos akan tampil di sini." />
            @else
                <x-empty-state icon="list" title="Belum ada peraturan" text="Peraturan akan tampil setelah pemilik mengonfirmasi kamar Anda." />
            @endif
        </div>
    @else
        <section class="kos-card" aria-label="Daftar peraturan">
            @foreach ($peraturan as $item)
                <div class="kos-row" style="justify-content: flex-start; align-items: flex-start">
                    <span class="font-extrabold">{{ $loop->iteration }}</span>
                    <div class="min-w-0">
                        @if ($item->judul)
                            <p class="kos-row-title">{{ $item->judul }}</p>
                        @endif
                        <p class="whitespace-pre-line">{{ $item->isi }}</p>
                        <p class="kos-row-sub mt-0.5">Diperbarui {{ $item->diperbarui_label }}</p>
                    </div>
                </div>
            @endforeach
        </section>
    @endif
</x-app-layout>
