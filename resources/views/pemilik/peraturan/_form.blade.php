{{-- Form bersama untuk tambah & edit peraturan. Variabel: $action, $method, $peraturan (opsional), $submitLabel --}}
@php $peraturan = $peraturan ?? null; @endphp

<form method="POST" action="{{ $action }}" class="space-y-5" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <x-input-label for="isi" value="Isi peraturan" />
        <textarea id="isi" name="isi" rows="3" class="kos-input mt-1" maxlength="1000" required autofocus
                  placeholder="Contoh: Tamu wajib melapor kepada pemilik/pengelola kos.">{{ old('isi', $peraturan?->isi) }}</textarea>
        <x-input-error :messages="$errors->get('isi')" />
    </div>

    <div>
        <x-input-label for="judul" value="Judul (opsional)" />
        <x-text-input id="judul" name="judul" type="text" class="mt-1"
                      :value="old('judul', $peraturan?->judul)" placeholder="Contoh: Tamu" maxlength="150" />
        <x-input-error :messages="$errors->get('judul')" />
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ route('pemilik.peraturan.index') }}" class="kos-btn kos-btn-outline">Batal</a>
    </div>
</form>
