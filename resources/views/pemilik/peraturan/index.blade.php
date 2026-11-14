<x-app-layout breadcrumb="Mengelola Peraturan Kos">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="kos-page-title">Mengelola Peraturan Kos</h1>
                <p class="kos-page-sub">Tambah, ubah, dan hapus peraturan.</p>
            </div>
            <a href="{{ route('pemilik.peraturan.create') }}" class="kos-btn kos-btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Tambah Peraturan
            </a>
        </div>
    </x-slot>

    @php $status = session('status'); @endphp

    @if ($status === 'peraturan-created')
        <x-alert>Peraturan berhasil ditambahkan.</x-alert>
    @elseif ($status === 'peraturan-updated')
        <x-alert>Peraturan berhasil diperbarui.</x-alert>
    @elseif ($status === 'peraturan-deleted')
        <x-alert>Peraturan berhasil dihapus.</x-alert>
    @endif

    @if ($peraturan->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="list" title="Belum ada peraturan" text="Klik “Tambah Peraturan” untuk membuat peraturan pertama.">
            </x-empty-state>
        </div>
    @else
        <section class="kos-card" aria-label="Daftar peraturan">
            @foreach ($peraturan as $item)
                <div class="kos-row">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="font-extrabold">{{ $loop->iteration }}</span>
                        <div class="min-w-0">
                            @if ($item->judul)
                                <p class="kos-row-title">{{ $item->judul }}</p>
                            @endif
                            <p class="whitespace-pre-line">{{ $item->isi }}</p>
                            <p class="kos-row-sub mt-0.5">Diperbarui {{ $item->diperbarui_label }}</p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <a href="{{ route('pemilik.peraturan.edit', $item) }}" class="kos-btn kos-btn-outline">Edit</a>

                        <form method="POST"
                              action="{{ route('pemilik.peraturan.destroy', $item) }}"
                              onsubmit="return confirm('Hapus peraturan nomor {{ $loop->iteration }}? Tindakan ini tidak bisa dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="kos-btn kos-btn-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </section>
    @endif
</x-app-layout>
