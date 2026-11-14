<x-app-layout breadcrumb="Informasi Kamar">
    <x-slot name="header">
        <h1 class="kos-page-title">Informasi Kamar</h1>
        <p class="kos-page-sub">Melihat informasi kamar penghuni.</p>
    </x-slot>

    @if (! $kamar)
        <div class="kos-card">
            <x-empty-state icon="door"
                           title="Kamar belum dikonfirmasi"
                           text="Informasi kamar akan tampil setelah pemilik kos mengonfirmasi kamar untuk Anda. Silakan hubungi pemilik kos." />
        </div>
    @else
        <article class="kos-card" style="padding: 0; overflow: hidden">
            <img src="{{ $kamar->foto_url }}" alt="Foto kamar {{ $kamar->nomor_kamar }}" class="kos-foto-kamar kos-foto-kamar-detail" decoding="async">

            <div class="p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="kos-row-sub">Nomor kamar</p>
                        <h2 class="text-2xl font-extrabold">{{ $kamar->nomor_kamar }}</h2>
                    </div>
                    <x-status-badge tone="success">Terisi</x-status-badge>
                </div>

                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="kos-card" style="box-shadow: none">
                        <dt class="kos-row-sub">Tipe kamar</dt>
                        <dd class="font-bold">{{ $kamar->tipe }}</dd>
                    </div>
                    <div class="kos-card" style="box-shadow: none">
                        <dt class="kos-row-sub">Harga sewa</dt>
                        <dd class="font-bold">{{ $kamar->harga_rupiah }} / bulan</dd>
                    </div>
                    <div class="kos-card sm:col-span-2" style="box-shadow: none">
                        <dt class="kos-row-sub">Fasilitas</dt>
                        <dd class="font-bold">{{ $kamar->fasilitas ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        </article>
    @endif
</x-app-layout>
