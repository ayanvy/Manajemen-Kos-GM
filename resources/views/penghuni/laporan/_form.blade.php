{{-- Form bersama untuk buat & edit laporan. Variabel: $action, $method, $submitLabel, $laporan (opsional) --}}
@php $laporan = $laporan ?? null; @endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <x-input-label for="deskripsi" value="Deskripsi kerusakan" />
        <textarea id="deskripsi" name="deskripsi" rows="5" maxlength="1000" required
                  class="kos-input mt-1 @error('deskripsi') kos-input-error @enderror"
                  placeholder="Contoh: AC kamar kurang dingin sejak kemarin.">{{ old('deskripsi', $laporan?->deskripsi) }}</textarea>
        <x-input-error :messages="$errors->get('deskripsi')" />
    </div>

    <div>
        <x-input-label for="foto" value="Foto bukti kerusakan (opsional)" />

        @if ($laporan?->foto)
            <div class="mt-2 space-y-2">
                <a href="{{ route('penghuni.laporan.foto', $laporan) }}" target="_blank" rel="noopener">
                    <img src="{{ route('penghuni.laporan.foto', $laporan) }}" alt="Foto kerusakan saat ini"
                         class="rounded-xl" style="max-width: 100%; max-height: 14rem">
                </a>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="hapus_foto" value="0">
                    <input type="checkbox" name="hapus_foto" value="1" @checked(old('hapus_foto'))>
                    Hapus foto ini
                </label>
                <p class="kos-row-sub">Pilih file baru di bawah kalau ingin mengganti foto.</p>
            </div>
        @endif

        <input id="foto" type="file" name="foto" accept=".jpg,.jpeg,.png,image/jpeg,image/png"
               class="kos-input mt-1 @error('foto') kos-input-error @enderror">
        <p class="kos-row-sub mt-1">Format JPG atau PNG. Maksimal 3 MB.</p>
        <x-input-error :messages="$errors->get('foto')" />
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ route('penghuni.laporan.index') }}" class="kos-btn kos-btn-outline">Batal</a>
    </div>
</form>
