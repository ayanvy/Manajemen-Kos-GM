@php
    $authUser = auth()->user();
    $breadcrumb = $attributes->get('breadcrumb', 'Dashboard');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $breadcrumb }} · KOS GM</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body x-data="{ sidebar: false }" x-on:keydown.escape.window="sidebar = false">
        {{-- Overlay untuk drawer di HP/tablet --}}
        <div class="kos-overlay lg:hidden" x-show="sidebar" x-cloak x-transition.opacity x-on:click="sidebar = false" aria-hidden="true"></div>

        @include('layouts.navigation')

        <div class="kos-main">
            <header class="kos-topbar">
                <div class="flex items-center gap-2">
                    <button type="button" class="kos-iconbtn kos-menu-btn" x-on:click="sidebar = true" aria-label="Buka menu" aria-controls="kos-sidebar">
                        <x-icon name="menu" class="h-5 w-5" />
                    </button>
                    <p class="kos-crumb">KOS GM / <strong>{{ $breadcrumb }}</strong></p>
                </div>

                <a href="{{ route('profile.edit') }}" class="kos-usermenu" title="Profil Saya">
                    <span class="hidden text-sm sm:block">
                        <span class="font-semibold">{{ $authUser->name }}</span>
                        <span style="color: var(--kos-muted)"> · {{ $authUser->role }}</span>
                    </span>
                    <x-user-avatar :user="$authUser" />
                </a>
            </header>

            <main class="kos-content">
                @isset($header)
                    <div>{{ $header }}</div>
                @endisset

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
