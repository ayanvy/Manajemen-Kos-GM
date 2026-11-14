@props(['type' => 'success'])

{{-- Pesan sukses hilang sendiri setelah 6 detik; pesan error tetap tampil sampai ditutup. --}}
<div {{ $attributes->merge(['class' => 'kos-alert kos-alert-' . $type]) }}
     role="{{ $type === 'error' ? 'alert' : 'status' }}"
     x-data="{ show: true }"
     @if ($type !== 'error') x-init="setTimeout(() => show = false, 6000)" @endif
     x-show="show"
     x-transition.opacity.duration.300ms>
    <x-icon :name="$type === 'error' ? 'alert-circle' : 'check-circle'" class="mt-0.5 h-4 w-4" />
    <div class="flex-1">{{ $slot }}</div>
    <button type="button" class="kos-alert-close" x-on:click="show = false" aria-label="Tutup pesan">&times;</button>
</div>
