{{-- Form bersama untuk buat & edit tagihan. Variabel: $action, $method, $tagihan (opsional), $submitLabel, $penghuniOptions --}}
@php
    $tagihan = $tagihan ?? null;

    // Peta id penghuni => harga kamar, dipakai untuk mengisi jumlah otomatis.
    $hargaKamar = $penghuniOptions->mapWithKeys(fn ($p) => [$p->id => $p->kamar ? (int) $p->kamar->harga : null]);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5" novalidate
      x-data="{
          harga: @js($hargaKamar),
          penghuni: @js((string) old('penghuni_id', $tagihan?->penghuni_id)),
          jumlah: @js((string) old('jumlah', $tagihan ? (int) $tagihan->jumlah : '')),
          pilihPenghuni() {
              // Isi jumlah dengan harga kamar penghuni, kecuali sedang mengedit tagihan yang sudah ada.
              @if (! $tagihan)
                  this.jumlah = this.harga[this.penghuni] ?? '';
              @endif
          },
      }">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-input-label for="penghuni_id" value="Penghuni" />
            <select id="penghuni_id" name="penghuni_id" class="kos-input mt-1" required
                    x-model="penghuni" x-on:change="pilihPenghuni()">
                <option value="">Pilih penghuni</option>
                @foreach ($penghuniOptions as $p)
                    <option value="{{ $p->id }}">
                        {{ $p->name }} &mdash; {{ $p->kamar ? 'Kamar '.$p->kamar->nomor_kamar : 'tanpa kamar' }}
                    </option>
                @endforeach
            </select>
            @if ($penghuniOptions->isEmpty())
                <p class="kos-row-sub mt-1">Belum ada penghuni yang menempati kamar. Konfirmasi penghuni di menu Mengelola Kamar terlebih dahulu.</p>
            @endif
            <x-input-error :messages="$errors->get('penghuni_id')" />
        </div>

        <div>
            <x-input-label for="periode_bulan" value="Periode" />
            <x-text-input id="periode_bulan" name="periode_bulan" type="month" class="mt-1"
                          :value="old('periode_bulan', $tagihan?->periode_bulan ?? now()->format('Y-m'))" required />
            <x-input-error :messages="$errors->get('periode_bulan')" />
        </div>

        <div>
            <x-input-label for="jatuh_tempo" value="Jatuh tempo" />
            <x-text-input id="jatuh_tempo" name="jatuh_tempo" type="date" class="mt-1"
                          :value="old('jatuh_tempo', $tagihan?->jatuh_tempo?->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('jatuh_tempo')" />
        </div>

        <div>
            <x-input-label for="jumlah" value="Jumlah tagihan (Rp)" />
            <x-text-input id="jumlah" name="jumlah" type="text" inputmode="numeric" class="mt-1"
                          x-model="jumlah" placeholder="Contoh: 900000" required />
            <p class="kos-row-sub mt-1">Otomatis terisi harga kamar saat penghuni dipilih, dan masih bisa diubah.</p>
            <x-input-error :messages="$errors->get('jumlah')" />
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ route('pemilik.tagihan.index') }}" class="kos-btn kos-btn-outline">Batal</a>
    </div>
</form>
