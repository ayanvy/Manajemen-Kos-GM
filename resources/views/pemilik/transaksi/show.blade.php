<x-app-layout breadcrumb="Riwayat Transaksi">
    <x-slot name="header">
        <h1 class="kos-page-title">Detail Transaksi</h1>
        <p class="kos-page-sub">Pembayaran tagihan periode {{ $pembayaran->tagihan->periode }}.</p>
    </x-slot>

    @include('pembayaran._detail', [
        'pembayaran' => $pembayaran,
        'buktiRoute' => 'pemilik.pembayaran.bukti',
        'kembaliRoute' => 'pemilik.transaksi.index',
        'tampilkanPenghuni' => true,
    ])
</x-app-layout>
