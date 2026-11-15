@props(['user', 'size' => 'sm'])

@php $initial = mb_strtoupper(mb_substr(trim($user->name), 0, 1)); @endphp

@if ($user->profile_photo_path)
    <img
        src="{{ str_starts_with($user->profile_photo_path, 'http')
        ? $user->profile_photo_path
        : asset('storage/' . $user->profile_photo_path) }}"
        alt="Foto profil {{ $user->name }}"
        {{ $attributes->merge(['class' => 'kos-avatar kos-avatar-' . $size]) }}
    />
@else
    <span {{ $attributes->merge(['class' => 'kos-avatar kos-avatar-' . $size]) }} aria-hidden="true">{{ $initial }}</span>
@endif
