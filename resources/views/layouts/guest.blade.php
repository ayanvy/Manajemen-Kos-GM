<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>KOS GM — Sistem Informasi Kos</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="kos-auth-grid">
            {{-- Panel kiri: identitas merek (di HP hanya tampil sebagai bar logo) --}}
            <aside class="kos-auth-brand">
                <x-brand-logo />

                <div class="kos-auth-body">
                    <h1 class="kos-auth-title">Manajemen kos yang lebih tertata.</h1>
                    <p class="kos-auth-desc">
                        Sistem informasi kos untuk memantau kamar, penghuni, tagihan, pembayaran,
                        laporan kerusakan, pengumuman, dan peraturan kos dalam satu tempat.
                    </p>
                </div>
            </aside>

            {{-- Panel kanan: kartu form --}}
            <main class="kos-auth-form">
                <div class="kos-auth-card">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
