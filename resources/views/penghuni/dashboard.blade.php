<x-app-layout breadcrumb="Dashboard">
    @php $namaDepan = \Illuminate\Support\Str::of(auth()->user()->name)->trim()->before(' '); @endphp

    <section class="kos-banner">
        <div>
            <p class="kos-banner-eyebrow">Dashboard Penghuni</p>
            <h1 class="kos-banner-title">Selamat datang, {{ $namaDepan }}</h1>
            <p class="kos-banner-desc">
                Kelola informasi kamar, tagihan, pembayaran, laporan kerusakan, pengumuman, dan peraturan kos dari satu tempat.
            </p>
            <div class="kos-banner-actions">
                <a href="{{ route('profile.edit') }}" class="kos-btn kos-btn-banner-light">Profil Saya</a>
                <a href="{{ route('penghuni.kamar') }}" class="kos-btn kos-btn-banner-dark">Lihat Kamar</a>
            </div>
        </div>
        <img src="{{ asset('images/kos-hero.jpg') }}" alt="Interior kamar kos" class="kos-banner-img">
    </section>

    {{-- Kamar, tagihan, pembayaran, dan laporan aktif: data nyata. --}}
    <section class="kos-grid-stats" aria-label="Ringkasan">
        @if ($kamar)
            <x-stat-card label="Kamar" icon="door" :value="$kamar->nomor_kamar" hint="Terisi" />
        @else
            <x-stat-card label="Kamar" icon="door" hint="Belum dikonfirmasi pemilik" />
        @endif
        <x-stat-card label="Tagihan belum lunas" icon="receipt" :value="$tagihanBelumLunas" hint="Perlu diperiksa" />
        <a href="{{ route('penghuni.pembayaran.status') }}" class="block" style="text-decoration: none; color: inherit"
           aria-label="Pembayaran menunggu verifikasi: {{ $pembayaranMenunggu }}. Buka status pembayaran">
            <x-stat-card label="Pembayaran" icon="wallet" :value="$pembayaranMenunggu"
                         :hint="$pembayaranMenunggu > 0
                             ? 'Menunggu verifikasi pemilik'
                             : ($pembayaranDitolak > 0 ? $pembayaranDitolak.' ditolak, kirim ulang' : 'Tidak ada yang menunggu')" />
        </a>
        <a href="{{ route('penghuni.laporan.status') }}" class="block" style="text-decoration: none; color: inherit"
           aria-label="Laporan aktif: {{ $laporanAktif }}. Buka status laporan">
            <x-stat-card label="Laporan aktif" icon="wrench" :value="$laporanAktif"
                         :hint="$laporanAktif > 0 ? 'Kerusakan belum selesai' : 'Tidak ada laporan aktif'" />
        </a>
    </section>

    <section class="kos-grid-2">
        <div class="kos-card">
            <div class="kos-card-head">
                <h2 class="kos-card-heading">Tagihan terbaru</h2>
            </div>
            @forelse ($tagihanTerbaru as $tagihan)
                <div class="kos-list-item">
                    <div>
                        <p class="font-semibold">{{ $tagihan->periode }}</p>
                        <p class="text-sm text-gray-500">
                            Jatuh tempo: {{ $tagihan->jatuh_tempo->format('d M Y') }}
                        </p>
                        <p class="text-sm font-medium">
                            Rp {{ number_format((float) $tagihan->jumlah, 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">
                            {{ $tagihan->status === \App\Models\Tagihan::STATUS_LUNAS ? 'Lunas' : 'Belum dibayar' }}
                        </p>
                        <a href="{{ route('penghuni.tagihan.show', $tagihan) }}"
                           class="text-sm underline">
                            Lihat detail
                        </a>
                    </div>
                </div>
            @empty
                <x-empty-state
                    icon="receipt"
                    title="Belum ada tagihan"
                    text="Tagihan kos Anda akan muncul di sini setelah pemilik membuat tagihan." />
            @endforelse
        </div>

        <div class="kos-card">
            <div class="kos-card-head">
                <h2 class="kos-card-heading">Pengumuman</h2>
                <a href="{{ route('penghuni.pengumuman.index') }}" class="kos-btn kos-btn-outline">Semua</a>
            </div>

            @forelse ($pengumumanTerbaru as $pengumuman)
                <a href="{{ route('penghuni.pengumuman.show', $pengumuman) }}" class="kos-row" style="text-decoration: none; color: inherit">
                    <div class="min-w-0">
                        <p class="kos-row-title truncate">{{ $pengumuman->judul }}</p>
                        <p class="kos-row-sub truncate">{{ $pengumuman->tgl_post_label }}</p>
                    </div>
                </a>
            @empty
                <x-empty-state icon="megaphone" title="Belum ada pengumuman" text="Pengumuman dari pemilik kos akan tampil di sini." />
            @endforelse
        </div>
    </section>
</x-app-layout>
