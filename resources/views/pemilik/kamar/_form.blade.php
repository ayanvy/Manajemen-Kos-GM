{{-- Form bersama untuk tambah & edit kamar. Variabel: $action, $method, $kamar (opsional), $submitLabel, $penghuniOptions --}}
@php
    $kamar = $kamar ?? null;
    $tipeContoh = ['Standard', 'Deluxe'];
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5" enctype="multipart/form-data" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <x-input-label for="nomor_kamar" value="Nomor kamar" />
            <x-text-input id="nomor_kamar" name="nomor_kamar" type="text" class="mt-1"
                          :value="old('nomor_kamar', $kamar?->nomor_kamar)" placeholder="Contoh: A-01" maxlength="20" required autofocus />
            <x-input-error :messages="$errors->get('nomor_kamar')" />
        </div>

        <div>
            <x-input-label for="tipe" value="Tipe kamar" />
            <x-text-input id="tipe" name="tipe" type="text" class="mt-1" list="daftar-tipe"
                          :value="old('tipe', $kamar?->tipe)" placeholder="Contoh: Standard" maxlength="50" required />
            <datalist id="daftar-tipe">
                @foreach ($tipeContoh as $t)
                    <option value="{{ $t }}"></option>
                @endforeach
            </datalist>
            <x-input-error :messages="$errors->get('tipe')" />
        </div>

        <div>
            <x-input-label for="harga" value="Harga sewa per bulan (Rp)" />
            <x-text-input id="harga" name="harga" type="text" inputmode="numeric" class="mt-1"
                          :value="old('harga', $kamar ? (int) $kamar->harga : null)" placeholder="Contoh: 900000" required />
            <x-input-error :messages="$errors->get('harga')" />
        </div>

        <div>
            <x-input-label for="penghuni_id" value="Penghuni (konfirmasi kamar)" />
            <select id="penghuni_id" name="penghuni_id" class="kos-input mt-1">
                <option value="">Kosong &mdash; kamar tersedia</option>
                @foreach ($penghuniOptions as $p)
                    <option value="{{ $p->id }}" @selected((string) old('penghuni_id', $kamar?->penghuni_id) === (string) $p->id)>{{ $p->name }} ({{ $p->email }})</option>
                @endforeach
            </select>
            <p class="kos-row-sub mt-1">Penghuni baru bisa melihat informasi kamar setelah dipilih di sini. Status kamar otomatis menjadi Terisi.</p>
            <x-input-error :messages="$errors->get('penghuni_id')" />
        </div>
    </div>

    <div>
        <x-input-label for="fasilitas" value="Fasilitas" />
        <textarea id="fasilitas" name="fasilitas" rows="3" class="kos-input mt-1"
                  placeholder="Pisahkan dengan koma. Contoh: Kasur, lemari, meja belajar, AC, Wi-Fi">{{ old('fasilitas', $kamar?->fasilitas) }}</textarea>
        <x-input-error :messages="$errors->get('fasilitas')" />
    </div>

    <div x-data="{ preview: null, hapus: false }">
        <x-input-label for="foto" value="Foto kamar (opsional)" />

        @if ($kamar?->foto)
            <div class="mt-2" x-show="! preview && ! hapus">
                <img src="{{ $kamar->foto_url }}" alt="Foto kamar {{ $kamar->nomor_kamar }}" class="kos-foto-kamar rounded-xl" style="max-width: 24rem">
            </div>
        @endif

        <img x-show="preview" x-cloak :src="preview" alt="Pratinjau foto kamar" class="kos-foto-kamar mt-2 rounded-xl" style="max-width: 24rem">

        <input id="foto" name="foto" type="file" x-ref="foto" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
               class="kos-input mt-2"
               x-on:change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : null; if (f) hapus = false">
        <p class="kos-row-sub mt-1">Format JPG, PNG, atau WebP. Maksimal 2 MB. Foto ini akan tampil di informasi kamar penghuni.</p>
        <x-input-error :messages="$errors->get('foto')" />

        @if ($kamar?->foto)
            <label class="mt-2 flex items-center gap-2 text-sm">
                <input type="checkbox" name="hapus_foto" value="1" x-model="hapus" x-on:change="if (hapus) { preview = null; $refs.foto.value = '' }">
                Hapus foto saat ini
            </label>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ route('pemilik.kamar.index') }}" class="kos-btn kos-btn-outline">Batal</a>
    </div>
</form>
