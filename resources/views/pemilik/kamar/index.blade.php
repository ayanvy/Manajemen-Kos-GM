<x-app-layout breadcrumb="Mengelola Kamar">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="kos-page-title">Mengelola Kamar</h1>
                <p class="kos-page-sub">Tambah, ubah, dan hapus data kamar.</p>
            </div>
            <a href="{{ route('pemilik.kamar.create') }}" class="kos-btn kos-btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Tambah Kamar
            </a>
        </div>
    </x-slot>

    @php $status = session('status'); @endphp

    @if ($status === 'kamar-created')
        <x-alert>Kamar baru berhasil ditambahkan.</x-alert>
    @elseif ($status === 'kamar-updated')
        <x-alert>Data kamar berhasil diperbarui.</x-alert>
    @elseif ($status === 'kamar-deleted')
        <x-alert>Kamar berhasil dihapus.</x-alert>
    @elseif ($status === 'kamar-terisi')
        <x-alert type="error">Kamar yang masih terisi tidak bisa dihapus. Kosongkan penghuninya dulu lewat Edit.</x-alert>
    @endif

    @if ($kamar->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="door" title="Belum ada kamar" text="Klik “Tambah Kamar” untuk membuat data kamar pertama.">
            </x-empty-state>
        </div>
    @else
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Daftar kamar">
            @foreach ($kamar as $item)
                <article class="kos-card" style="padding: 0; overflow: hidden">
                    <img src="{{ $item->foto_url }}" alt="Foto kamar {{ $item->nomor_kamar }}" class="kos-foto-kamar" loading="lazy" decoding="async">

                    <div class="p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-extrabold">{{ $item->nomor_kamar }}</h2>
                            <x-status-badge :tone="$item->isTerisi() ? 'danger' : 'success'">
                                {{ \App\Models\Kamar::STATUS[$item->status] ?? $item->status }}
                            </x-status-badge>
                        </div>

                        <p class="kos-row-sub mt-1">
                            {{ $item->tipe }}@if ($item->fasilitas) &middot; {{ $item->fasilitas }}@endif
                        </p>

                        <p class="kos-row-sub mt-1">
                            @if ($item->penghuni) Penghuni: {{ $item->penghuni->name }} @else Belum ada penghuni @endif
                        </p>

                        <p class="mt-3 text-base font-extrabold">{{ $item->harga_rupiah }}</p>

                        <div class="mt-4 flex items-center gap-2">
                            <a href="{{ route('pemilik.kamar.edit', $item) }}" class="kos-btn kos-btn-outline">Edit</a>

                            <form method="POST"
                                  action="{{ route('pemilik.kamar.destroy', $item) }}"
                                  onsubmit="return confirm('Hapus kamar {{ e($item->nomor_kamar) }}? Tindakan ini tidak bisa dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="kos-btn kos-btn-danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</x-app-layout>
