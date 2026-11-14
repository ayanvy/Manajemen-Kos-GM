@props(['value'])

<label {{ $attributes->merge(['class' => 'kos-label']) }}>
    {{ $value ?? $slot }}
</label>
