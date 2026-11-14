@props(['id'])

{{-- Input password dengan tombol mata DI DALAM kolom (wrapper relative + tombol absolute kanan). --}}
@php
    $field = $attributes->get('name', $id);
    $invalid = isset($errors) && $errors->has($field);
@endphp

<div x-data="{ show: false }" class="kos-pw relative">
    <input
        id="{{ $id }}"
        type="password"
        x-bind:type="show ? 'text' : 'password'"
        @if ($invalid) aria-invalid="true" @endif
        {{ $attributes->merge(['name' => $id, 'class' => 'kos-input kos-input-pw pr-11']) }}
    >

    <button
        type="button"
        class="kos-pw-toggle absolute inset-y-0 right-0"
        x-on:click="show = ! show"
        x-bind:aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
        x-bind:aria-pressed="show ? 'true' : 'false'"
        aria-label="Tampilkan password"
        aria-pressed="false"
        aria-controls="{{ $id }}"
    >
        {{-- Password tersembunyi: mata dengan garis diagonal. Password terlihat: mata biasa. --}}
        <x-icon name="eye-off" class="h-5 w-5" x-show="! show" />
        <x-icon name="eye" class="h-5 w-5" x-show="show" x-cloak />
    </button>
</div>
