<x-app-layout breadcrumb="Mengelola Laporan">
    <x-slot name="header">
        <h1 class="kos-page-title">Detail Laporan Kerusakan</h1>
        <p class="kos-page-sub">Laporan dari {{ $laporan->nama_penghuni_tampil }}, kamar {{ $laporan->nomor_kamar_tampil }}.</p>
    </x-slot>

    @php $status = session('status'); @endphp
    @if ($status === 'laporan-updated')
        <x-alert>Laporan berhasil diperbarui.</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    @php $bisaDikelola = $laporan->dapatDikelolaOleh(auth()->id()) && ! $laporan->isSelesai(); @endphp

    <article class="kos-card" style="max-width: 44rem">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="kos-row-sub">Deskripsi kerusakan</p>
                <p class="mt-1 whitespace-pre-line">{{ $laporan->deskripsi }}</p>
            </div>
            <x-status-badge :tone="$laporan->status_tone">{{ $laporan->status_label }}</x-status-badge>
        </div>

        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Penghuni</dt>
                <dd class="font-bold">{{ $laporan->nama_penghuni_tampil }}</dd>
            </div>
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Kamar</dt>
                <dd class="font-bold">{{ $laporan->nomor_kamar_tampil }}</dd>
            </div>
            <div class="kos-card" style="box-shadow: none">
                <dt class="kos-row-sub">Tanggal lapor</dt>
                <dd class="font-bold">{{ $laporan->tgl_lapor_label }}</dd>
            </div>
            @if ($laporan->tgl_selesai_label)
                <div class="kos-card" style="box-shadow: none">
                    <dt class="kos-row-sub">Tanggal selesai</dt>
                    <dd class="font-bold">{{ $laporan->tgl_selesai_label }}</dd>
                </div>
            @endif
        </dl>

        @if ($laporan->foto && $laporan->isMilikPemilik(auth()->id()))
            <div class="mt-4">
                <p class="kos-row-sub">Foto bukti</p>
                <a href="{{ route('pemilik.laporan.foto', $laporan) }}" target="_blank" rel="noopener">
                    <img src="{{ route('pemilik.laporan.foto', $laporan) }}" alt="Foto kerusakan"
                         class="mt-2 rounded-xl" style="max-width: 100%; max-height: 24rem">
                </a>
            </div>
        @endif

        <x-laporan-stepper :laporan="$laporan" class="mt-4" />

        @if ($laporan->isArsip())
            <p class="kos-row-sub mt-4">Arsip &middot; hanya-baca. Akun penghuni atau pemilik laporan ini sudah dihapus.</p>
        @endif

        @if ($bisaDikelola)
            <form method="POST" action="{{ route('pemilik.laporan.update', $laporan) }}" class="mt-5 space-y-4"
                  x-data="{ status: @js(old('status', $laporan->status)) }"
                  x-on:submit="if (status === 'selesai' && ! confirm('Tandai laporan ini selesai? Setelah selesai, status dan catatan tidak bisa diubah lagi.')) $event.preventDefault()">
                @csrf
                @method('PATCH')

                <div>
                    <x-input-label for="status" value="Status laporan" />
                    <select id="status" name="status" class="kos-input mt-1 @error('status') kos-input-error @enderror"
                            x-model="status" required>
                        @foreach ($laporan->statusTujuan() as $kode => $label)
                            <option value="{{ $kode }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="kos-row-sub mt-1">Status hanya bisa maju, tidak bisa dikembalikan ke tahap sebelumnya.</p>
                    <x-input-error :messages="$errors->get('status')" />
                </div>

                <div>
                    <x-input-label for="catatan_pemilik" value="Catatan untuk penghuni (opsional)" />
                    <textarea id="catatan_pemilik" name="catatan_pemilik" rows="3" maxlength="500"
                              class="kos-input mt-1 @error('catatan_pemilik') kos-input-error @enderror"
                              placeholder="Contoh: teknisi AC datang besok pagi.">{{ old('catatan_pemilik', $laporan->catatan_pemilik) }}</textarea>
                    <x-input-error :messages="$errors->get('catatan_pemilik')" />
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('pemilik.laporan.index') }}" class="kos-btn kos-btn-outline">Kembali</a>
                </div>
            </form>
        @else
            @if ($laporan->catatan_pemilik)
                <div class="mt-4 rounded-xl p-3 text-sm" style="background: var(--kos-50)">
                    <p class="font-bold">Catatan pemilik</p>
                    <p class="mt-1 whitespace-pre-line">{{ $laporan->catatan_pemilik }}</p>
                </div>
            @endif

            <div class="mt-5">
                <a href="{{ route('pemilik.laporan.index') }}" class="kos-btn kos-btn-outline">Kembali</a>
            </div>
        @endif
    </article>
</x-app-layout>
