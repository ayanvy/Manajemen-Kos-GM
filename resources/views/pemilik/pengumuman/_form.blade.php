{{-- Form bersama untuk tambah & edit pengumuman. Variabel: $action, $method, $pengumuman (opsional), $submitLabel --}}
@php $pengumuman = $pengumuman ?? null; @endphp

<form method="POST" action="{{ $action }}" class="space-y-5" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <x-input-label for="judul" value="Judul" />
        <x-text-input id="judul" name="judul" type="text" class="mt-1"
                      :value="old('judul', $pengumuman?->judul)" placeholder="Contoh: Jadwal kebersihan lingkungan kos" maxlength="150" required autofocus />
        <x-input-error :messages="$errors->get('judul')" />
    </div>

    <div>
        <x-input-label for="isi" value="Isi pengumuman" />
        <textarea id="isi" name="isi" rows="6" class="kos-input mt-1" maxlength="5000" required
                  placeholder="Tulis isi pengumuman untuk penghuni.">{{ old('isi', $pengumuman?->isi) }}</textarea>
        <x-input-error :messages="$errors->get('isi')" />
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ route('pemilik.pengumuman.index') }}" class="kos-btn kos-btn-outline">Batal</a>
    </div>
</form>
