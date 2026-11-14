<x-app-layout breadcrumb="Memverifikasi Pembayaran">
    <x-slot name="header">
        <h1 class="kos-page-title">Memverifikasi Pembayaran</h1>
        <p class="kos-page-sub">Periksa bukti pembayaran dan tentukan status verifikasi.</p>
    </x-slot>

    @php $status = session('status'); @endphp
    @if ($status === 'pembayaran-accepted')
        <x-alert>Pembayaran diterima dan tagihan ditandai lunas.</x-alert>
    @elseif ($status === 'pembayaran-rejected')
        <x-alert>Pembayaran ditolak. Penghuni dapat mengirim bukti yang baru.</x-alert>
    @endif
    @if (session('error'))
        <div class="kos-card" role="alert" style="background: var(--kos-danger-bg); color: var(--kos-danger); margin-bottom: 1rem">{{ session('error') }}</div>
    @endif

    @if ($pembayaran->isEmpty())
        <div class="kos-card">
            <x-empty-state icon="badge-check" title="Tidak ada pembayaran yang menunggu" text="Pembayaran baru dari penghuni akan muncul di sini untuk diverifikasi." />
        </div>
    @else
        <div class="space-y-4" style="max-width: 52rem">
            @foreach ($pembayaran as $item)
                <article class="kos-card" x-data="{ tolak: {{ $errors->has('alasan_penolakan') && (int) old('pembayaran_id') === $item->id ? 'true' : 'false' }} }">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-extrabold">{{ $item->tagihan->nama_penghuni_tampil }}</h2>
                            <p class="kos-row-sub">{{ $item->tagihan->penghuni?->kamar?->nomor_kamar ?? '-' }} &middot; {{ $item->tagihan->periode }}</p>
                        </div>
                        <x-status-badge :tone="$item->status_tone">{{ $item->status_label }}</x-status-badge>
                    </div>

                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="kos-card" style="box-shadow: none">
                            <dt class="kos-row-sub">Jumlah bayar</dt>
                            <dd class="font-bold">{{ $item->jumlah_rupiah }}</dd>
                        </div>
                        <div class="kos-card" style="box-shadow: none">
                            <dt class="kos-row-sub">Metode</dt>
                            <dd class="font-bold">{{ $item->metode_label }}</dd>
                        </div>
                        <div class="kos-card" style="box-shadow: none">
                            <dt class="kos-row-sub">Tanggal</dt>
                            <dd class="font-bold">{{ $item->tgl_bayar_label }}</dd>
                        </div>
                        <div class="kos-card" style="box-shadow: none">
                            <dt class="kos-row-sub">Jumlah tagihan</dt>
                            <dd class="font-bold">{{ $item->tagihan->jumlah_rupiah }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <a href="{{ route('pemilik.pembayaran.bukti', $item) }}" target="_blank" rel="noopener" class="kos-btn kos-btn-outline">Lihat Bukti</a>

                        <form method="POST" action="{{ route('pemilik.pembayaran.terima', $item) }}"
                              onsubmit="return confirm(@js('Terima pembayaran ' . $item->tagihan->periode . ' dari ' . $item->tagihan->nama_penghuni_tampil . '? Tagihan akan ditandai lunas.'))">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="kos-btn kos-btn-primary">Terima</button>
                        </form>

                        <button type="button" class="kos-btn kos-btn-danger" x-on:click="tolak = !tolak" x-bind:aria-expanded="tolak">Tolak</button>
                    </div>

                    <form method="POST" action="{{ route('pemilik.pembayaran.tolak', $item) }}" class="mt-4 space-y-2" x-show="tolak" x-cloak>
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="pembayaran_id" value="{{ $item->id }}">
                        <label for="alasan-{{ $item->id }}" class="kos-label">Alasan penolakan</label>
                        <textarea id="alasan-{{ $item->id }}" name="alasan_penolakan" rows="3" maxlength="500"
                                  class="kos-input @error('alasan_penolakan') kos-input-error @enderror"
                                  placeholder="Contoh: bukti tidak terbaca atau nominal tidak sesuai.">{{ (int) old('pembayaran_id') === $item->id ? old('alasan_penolakan') : '' }}</textarea>
                        @if ((int) old('pembayaran_id') === $item->id)
                            @error('alasan_penolakan') <p class="kos-error">{{ $message }}</p> @enderror
                        @endif
                        <button type="submit" class="kos-btn kos-btn-danger">Kirim Penolakan</button>
                    </form>
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
