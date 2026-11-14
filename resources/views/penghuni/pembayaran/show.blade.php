<x-app-layout breadcrumb="Riwayat Pembayaran">
    <x-slot name="header">
        <h1 class="kos-page-title">Detail Pembayaran</h1>
        <p class="kos-page-sub">Pembayaran tagihan periode {{ $pembayaran->tagihan->periode }}.</p>
    </x-slot>

    @include('pembayaran._detail', [
        'pembayaran' => $pembayaran,
        'buktiRoute' => 'penghuni.pembayaran.bukti',
        'kembaliRoute' => 'penghuni.pembayaran.riwayat',
        'tampilkanPenghuni' => false,
    ])
</x-app-layout>
