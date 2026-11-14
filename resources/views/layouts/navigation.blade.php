{{--
    Sidebar KOS GM (dipakai oleh layouts/app.blade.php).

    Menu hanya menjadi tautan jika route-nya sudah ada. Menu yang fiturnya belum dibuat
    ditampilkan nonaktif (bukan tautan palsu). Saat sebuah fitur selesai, isi nama route-nya
    pada kolom ke-3 di bawah, contoh: ['Mengelola Kamar', 'door', 'kamar.index'].
--}}
@php
    $isPemilik = $authUser->role === \App\Models\User::ROLE_PEMILIK;

    $menu = $isPemilik
        ? [
            ['Dashboard', 'home', 'pemilik.dashboard'],
            ['Mengelola Kamar', 'door', 'pemilik.kamar.index'],
            ['Mengelola Penghuni', 'users', 'pemilik.penghuni.index'],
            ['Mengelola Tagihan', 'receipt', 'pemilik.tagihan.index'],
            ['Memverifikasi Pembayaran', 'badge-check', 'pemilik.pembayaran.index'],
            ['Riwayat Transaksi', 'history', 'pemilik.transaksi.index'],
            ['Mengelola Laporan', 'wrench', 'pemilik.laporan.index'],
            ['Mengelola Pengumuman', 'megaphone', 'pemilik.pengumuman.index'],
            ['Mengelola Peraturan Kos', 'list', 'pemilik.peraturan.index'],
        ]
        : [
            ['Dashboard', 'home', 'penghuni.dashboard'],
            ['Informasi Kamar', 'door', 'penghuni.kamar'],
            ['Tagihan', 'receipt', 'penghuni.tagihan.index'],
            ['Melakukan Pembayaran', 'wallet', 'penghuni.pembayaran.create'],
            ['Status Pembayaran', 'clock', 'penghuni.pembayaran.status'],
            ['Riwayat Pembayaran', 'history', 'penghuni.pembayaran.riwayat'],
            ['Laporan Kerusakan', 'wrench', 'penghuni.laporan.index'],
            ['Status Laporan', 'clipboard', 'penghuni.laporan.status'],
            ['Pengumuman', 'megaphone', 'penghuni.pengumuman.index'],
            ['Peraturan Kos', 'list', 'penghuni.peraturan.index'],
        ];
@endphp

<aside id="kos-sidebar" class="kos-sidebar" x-bind:class="{ 'is-open': sidebar }" aria-label="Menu utama">
    <div class="flex items-center justify-between">
        <x-brand-logo />
        <button type="button" class="kos-iconbtn lg:hidden" style="color: #cfe0d5" x-on:click="sidebar = false" aria-label="Tutup menu">
            <x-icon name="x" class="h-5 w-5" />
        </button>
    </div>

    <p class="kos-nav-caption">{{ $isPemilik ? 'Menu Pemilik' : 'Menu Penghuni' }}</p>

    <nav class="kos-nav-list">
        @foreach ($menu as [$label, $icon, $routeName])
            @if ($routeName && Route::has($routeName))
                @php $active = request()->routeIs($routeName) || ($loop->first && request()->routeIs('dashboard')); @endphp
                <a href="{{ route($routeName) }}"
                   class="kos-nav {{ $active ? 'kos-nav-active' : '' }}"
                   @if ($active) aria-current="page" @endif>
                    <x-icon :name="$icon" class="h-[18px] w-[18px]" />
                    <span>{{ $label }}</span>
                </a>
            @else
                <span class="kos-nav kos-nav-disabled" aria-disabled="true" title="Fitur ini belum tersedia">
                    <x-icon :name="$icon" class="h-[18px] w-[18px]" />
                    <span>{{ $label }}</span>
                </span>
            @endif
        @endforeach
    </nav>

    <div class="kos-sidebar-user">
        <p class="text-xs" style="color: #9fbfab">Login sebagai</p>
        <p class="mt-0.5 truncate font-bold text-white">{{ $authUser->name }}</p>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="kos-btn kos-btn-soft kos-btn-block" style="padding-top: .6rem; padding-bottom: .6rem">
                <x-icon name="logout" class="h-4 w-4" />
                Keluar
            </button>
        </form>
    </div>
</aside>

