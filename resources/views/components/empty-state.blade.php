@props(['icon' => 'inbox', 'title', 'text' => null])

<div {{ $attributes->merge(['class' => 'kos-empty']) }}>
    <span class="kos-empty-icon"><x-icon :name="$icon" class="h-5 w-5" /></span>
    <p class="font-semibold" style="color: var(--kos-ink)">{{ $title }}</p>
    @if ($text)
        <p class="mt-1">{{ $text }}</p>
    @endif
</div>
