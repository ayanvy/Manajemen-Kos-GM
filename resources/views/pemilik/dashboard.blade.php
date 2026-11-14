<x-app-layout breadcrumb="Dashboard">
    <section class="kos-banner">
        <div>
            <p class="kos-banner-eyebrow">Dashboard Pemilik</p>
            <h1 class="kos-banner-title">Kelola kos dengan lebih teratur.</h1>
            <p class="kos-banner-desc">
                Pantau kamar, penghuni, tagihan, pembayaran, laporan kerusakan, pengumuman, dan peraturan kos.
            </p>
            <div class="kos-banner-actions">
                <a href="{{ route('profile.edit') }}" class="kos-btn kos-btn-banner-light">Profil Saya</a>
                <a href="{{ route('pemilik.kamar.index') }}" class="kos-btn kos-btn-banner-dark">Kelola Kamar</a>
            </div>
        </div>
        <img src="{{ asset('images/kos-hero.jpg') }}" alt="Interior kamar kos" class="kos-banner-img">
    </section>

    {{-- Kamar, menunggu verifikasi, dan laporan aktif: data nyata. --}}
    <section class="kos-grid-stats" aria-label="Ringkasan">
        <x-stat-card label="Kamar terisi" icon="door" :value="$kamarTerisi" :hint="'dari '.$kamarTotal.' kamar'" />
        <x-stat-card label="Kamar tersedia" icon="plus" :value="$kamarTersedia" hint="Siap dihuni" />
        <a href="{{ route('pemilik.pembayaran.index') }}" class="block" style="text-decoration: none; color: inherit"
           aria-label="Menunggu verifikasi: {{ $menungguVerifikasi }}. Buka halaman verifikasi pembayaran">
            <x-stat-card label="Menunggu verifikasi" icon="badge-check" :value="$menungguVerifikasi"
                         :hint="$menungguVerifikasi > 0 ? 'Perlu dicek sekarang' : 'Tidak ada yang menunggu'" />
        </a>
        <a href="{{ route('pemilik.laporan.index') }}" class="block" style="text-decoration: none; color: inherit"
           aria-label="Laporan aktif: {{ $laporanAktif }}. Buka halaman mengelola laporan">
            <x-stat-card label="Laporan aktif" icon="wrench" :value="$laporanAktif"
                         :hint="$laporanAktif > 0 ? 'Perlu diproses' : 'Tidak ada laporan aktif'" />
        </a>
    </section>

    <section class="kos-grid-2">
        {{-- Data nyata: penghuni yang terdaftar di tabel users --}}
        <div class="kos-card">
            <div class="kos-card-head">
                <div>
                    <h2 class="kos-card-heading">Aktivitas terbaru</h2>
                    <p class="kos-card-sub">Penghuni yang baru mendaftar &middot; total {{ $penghuniTotal }} penghuni</p>
                </div>
            </div>

            @forelse ($penghuniBaru as $penghuni)
                <div class="kos-row">
                    <div class="flex min-w-0 items-center gap-3">
                        <x-user-avatar :user="$penghuni" />
                        <div class="min-w-0">
                            <p class="kos-row-title truncate">{{ $penghuni->name }}</p>
                            <p class="kos-row-sub truncate">Mendaftar {{ $penghuni->created_at?->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <x-status-badge tone="success">Penghuni baru</x-status-badge>
                </div>
            @empty
                <x-empty-state title="Belum ada aktivitas" text="Penghuni yang mendaftar akan muncul di sini." />
            @endforelse
        </div>

        <div class="kos-card">
            <div class="kos-card-head">
                <h2 class="kos-card-heading">Laporan terbaru</h2>
                <a href="{{ route('pemilik.laporan.index') }}" class="kos-btn kos-btn-outline">Kelola</a>
            </div>

            @forelse ($laporanTerbaru as $laporan)
                <a href="{{ route('pemilik.laporan.show', $laporan) }}" class="kos-row" style="text-decoration: none; color: inherit">
                    <div class="min-w-0">
                        <p class="kos-row-title truncate">{{ \Illuminate\Support\Str::limit($laporan->deskripsi, 60) }}</p>
                        <p class="kos-row-sub truncate">{{ $laporan->nama_penghuni_tampil }} &middot; kamar {{ $laporan->nomor_kamar_tampil }} &middot; {{ $laporan->tgl_lapor_label }}</p>
                    </div>
                    <x-status-badge :tone="$laporan->status_tone">{{ $laporan->status_label }}</x-status-badge>
                </a>
            @empty
                <x-empty-state icon="wrench" title="Belum ada laporan" text="Laporan kerusakan dari penghuni akan tampil di sini." />
            @endforelse
        </div>
    </section>
</x-app-layout>
