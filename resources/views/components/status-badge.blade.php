@props(['tone' => 'neutral'])

<span {{ $attributes->merge(['class' => 'kos-badge kos-badge-' . $tone]) }}>{{ $slot }}</span>
