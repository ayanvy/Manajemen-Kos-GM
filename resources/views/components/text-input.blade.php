@props(['disabled' => false])

@php
    $field = $attributes->get('name');
    $invalid = $field && isset($errors) && $errors->has($field);
@endphp

<input @disabled($disabled) @if ($invalid) aria-invalid="true" @endif {{ $attributes->merge(['class' => 'kos-input']) }}>
