@props(['label', 'icon', 'value' => null, 'hint' => null])

<div {{ $attributes->merge(['class' => 'kos-stat']) }}>
    <div class="kos-stat-top">
        <p class="kos-stat-label">{{ $label }}</p>
        <span class="kos-stat-icon"><x-icon :name="$icon" class="h-5 w-5" /></span>
    </div>

    @if ($value !== null)
        <p class="kos-stat-value">{{ $value }}</p>
    @else
        <p class="kos-stat-value kos-stat-value-empty" aria-label="Belum tersedia">&mdash;</p>
    @endif

    @if ($hint)
        <p class="kos-stat-hint">{{ $hint }}</p>
    @endif
</div>
