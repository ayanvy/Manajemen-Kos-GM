<x-app-layout breadcrumb="Melakukan Pembayaran">
    <x-slot name="header">
        <h1 class="kos-page-title">Melakukan Pembayaran</h1>
        <p class="kos-page-sub">Pilih tagihan, metode pembayaran, dan kirim bukti pembayaran.</p>
    </x-slot>

    @if (! $tagihan)
        <div class="kos-card">
            <x-empty-state icon="wallet" title="Tidak ada tagihan yang perlu dibayar" text="Semua tagihan Anda sudah lunas atau sedang menunggu verifikasi pemilik." />
        </div>
    @else
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_24rem]">
            <section class="kos-card">
                <p class="kos-row-sub">Tagihan yang dipilih</p>
                <h2 class="mt-2 text-2xl font-extrabold">{{ $tagihan->periode }}</h2>
                <p class="mt-3 text-4xl font-extrabold">{{ $tagihan->jumlah_rupiah }}</p>
                <p class="kos-row-sub mt-3">
                    Jatuh tempo: {{ $tagihan->jatuh_tempo_label }}
                    @if ($tagihan->isTerlambat()) &middot; <span style="color: var(--kos-danger); font-weight: 700">Terlambat</span> @endif
                </p>

                @if ($pembayaranDitolak)
                    <div class="mt-4 rounded-xl p-3 text-sm" style="background: var(--kos-danger-bg); color: var(--kos-danger)">
                        <p class="font-bold">Pembayaran sebelumnya ditolak</p>
                        <p class="mt-1">{{ $pembayaranDitolak->alasan_penolakan ?: 'Tidak ada alasan yang dicantumkan.' }}</p>
                        <p class="mt-1">Silakan kirim ulang dengan bukti yang benar.</p>
                    </div>
                @endif

                @if ($tagihanLain->isNotEmpty())
                    <div class="mt-5">
                        <p class="kos-row-sub">Tagihan lain yang belum dibayar</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($tagihanLain as $lain)
                                <a href="{{ route('penghuni.pembayaran.create', ['tagihan' => $lain->id]) }}" class="kos-btn kos-btn-outline">
                                    {{ $lain->periode }} &middot; {{ $lain->jumlah_rupiah }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>

            <form method="POST" action="{{ route('penghuni.pembayaran.store') }}" enctype="multipart/form-data" class="kos-card space-y-4" novalidate>
                @csrf
                <input type="hidden" name="tagihan_id" value="{{ $tagihan->id }}">
                @error('tagihan_id') <p class="kos-error">{{ $message }}</p> @enderror

                <div>
                    <label for="metode" class="kos-label">Metode pembayaran</label>
                    <select id="metode" name="metode" class="kos-input @error('metode') kos-input-error @enderror" required>
                        @foreach (\App\Models\Pembayaran::METODE as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(old('metode', 'transfer_bank') === $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('metode') <p class="kos-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="bukti" class="kos-label">Bukti bayar</label>
                    <input id="bukti" type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                           class="kos-input @error('bukti') kos-input-error @enderror" required>
                    <p class="kos-row-sub mt-1">Format JPG, PNG, atau PDF. Maksimal 3 MB.</p>
                    @error('bukti') <p class="kos-error">{{ $message }}</p> @enderror
                </div>

                <p class="rounded-xl p-3 text-sm" style="background: var(--kos-50)">
                    Setelah dikirim, pembayaran akan berstatus <strong>Menunggu</strong> sampai diverifikasi pemilik.
                </p>

                <button type="submit" class="kos-btn kos-btn-primary kos-btn-block">Kirim Pembayaran</button>
            </form>
        </div>
    @endif
</x-app-layout>
