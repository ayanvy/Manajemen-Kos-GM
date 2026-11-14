@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'kos-alert kos-alert-success']) }} role="status">
        <x-icon name="check-circle" class="mt-0.5 h-4 w-4" />
        <span>{{ $status }}</span>
    </div>
@endif
