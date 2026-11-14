@props(['laporan'])

@php
    $aktif = 'background: var(--kos-700); color: #fff';
    $posisi = array_search($laporan->status, array_keys(\App\Models\LaporanKerusakan::STATUS), true);
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }}>
    @foreach (\App\Models\LaporanKerusakan::STATUS as $kode => $label)
        @if (! $loop->first)
            <span aria-hidden="true">→</span>
        @endif
        @php $sudah = $loop->index <= $posisi; @endphp
        <span class="kos-badge {{ $sudah ? '' : 'kos-badge-neutral' }}" style="{{ $sudah ? $aktif : '' }}">{{ $label }}</span>
    @endforeach
</div>
