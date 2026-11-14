<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Pemilik melihat seluruh transaksi pembayaran, dengan filter periode dan penghuni. */
class RiwayatTransaksiController extends Controller
{
    public function index(Request $request): View
    {
        $pemilikId = $request->user()->id;

        $periode = (string) $request->query('periode', '');
        $penghuniId = (string) $request->query('penghuni', '');

        $transaksi = Pembayaran::untukPemilik($pemilikId)
            ->with('tagihan.penghuni.kamar')
            ->when($periode !== '', fn ($q) => $q->whereHas('tagihan', fn ($t) => $t->where('periode', $periode)))
            ->when($penghuniId !== '', fn ($q) => $q->whereHas('tagihan', fn ($t) => $t->where('penghuni_id', (int) $penghuniId)))
            ->orderByDesc('tgl_bayar')
            ->orderByDesc('id')
            ->get();

        $tagihanPemilik = Tagihan::where('pemilik_id', $pemilikId);

        $periodeOptions = (clone $tagihanPemilik)->whereHas('pembayaran')
            ->orderByDesc('jatuh_tempo')
            ->pluck('periode')
            ->unique()
            ->values();

        $penghuniOptions = (clone $tagihanPemilik)->whereHas('pembayaran')
            ->whereNotNull('penghuni_id')
            ->orderBy('nama_penghuni')
            ->get(['penghuni_id', 'nama_penghuni'])
            ->unique('penghuni_id')
            ->values();

        return view('pemilik.transaksi.index', compact(
            'transaksi', 'periodeOptions', 'penghuniOptions', 'periode', 'penghuniId'
        ));
    }

    public function show(Request $request, Pembayaran $pembayaran): View
    {
        $pembayaran->load('tagihan.penghuni.kamar', 'verifikator');

        abort_unless($pembayaran->tagihan?->isMilikPemilik($request->user()->id), 404);

        return view('pemilik.transaksi.show', compact('pembayaran'));
    }
}
