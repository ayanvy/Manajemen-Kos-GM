<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\LaporanKerusakan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/** Pemilik melihat laporan kerusakan penghuni dan memperbarui statusnya (hanya maju). */
class LaporanKerusakanController extends Controller
{
    /** Jumlah percobaan transaksi (percobaan ulang hanya terjadi saat deadlock). */
    private const PERCOBAAN_TRANSAKSI = 3;

    public function index(Request $request): View
    {
        // Laporan milik pemilik saat ini + arsip pemilik sebelumnya (pemilik_id null).
        $laporan = LaporanKerusakan::tampilUntukPemilik($request->user()->id)
            ->orderByDesc('tgl_lapor')
            ->orderByDesc('id')
            ->get();

        return view('pemilik.laporan.index', compact('laporan'));
    }

    public function show(Request $request, LaporanKerusakan $laporan): View
    {
        abort_unless($this->bolehDilihat($request, $laporan), 404);

        return view('pemilik.laporan.show', compact('laporan'));
    }

    public function update(Request $request, LaporanKerusakan $laporan): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(LaporanKerusakan::STATUS))],
            'catatan_pemilik' => ['nullable', 'string', 'max:500'],
        ], [
            'status.required' => 'Pilih status laporan.',
            'status.in' => 'Status laporan tidak valid.',
            'catatan_pemilik.max' => 'Catatan maksimal 500 karakter.',
        ]);

        return DB::transaction(function () use ($request, $laporan, $data) {
            // Kunci baris dulu, lalu cek ulang kepemilikan dan status dari data yang terkunci.
            $terkunci = LaporanKerusakan::whereKey($laporan->getKey())->lockForUpdate()->first();

            // Arsip dan laporan milik pemilik lain ditolak di server, bukan hanya disembunyikan tombolnya.
            abort_unless($terkunci?->dapatDikelolaOleh($request->user()->id), 404);

            if ($terkunci->isSelesai()) {
                return redirect()->route('pemilik.laporan.show', $terkunci)
                    ->with('error', 'Laporan ini sudah selesai dan tidak bisa diubah lagi.');
            }

            // Status hanya boleh tetap atau maju.
            if (! $terkunci->dapatPindahKe($data['status'])) {
                return redirect()->route('pemilik.laporan.show', $terkunci)
                    ->withInput()
                    ->withErrors(['status' => 'Status tidak bisa dimundurkan dari "'.$terkunci->status_label.'".']);
            }

            $terkunci->status = $data['status'];
            $terkunci->catatan_pemilik = $data['catatan_pemilik'] ?? null;

            if ($terkunci->isSelesai()) {
                $terkunci->tgl_selesai = now();
            }

            $terkunci->save();

            return redirect()->route('pemilik.laporan.show', $terkunci)
                ->with('status', 'laporan-updated');
        }, self::PERCOBAAN_TRANSAKSI);
    }

    public function foto(Request $request, LaporanKerusakan $laporan): Response
    {
        // Sama seperti bukti pembayaran: foto hanya untuk pemilik yang masih memiliki laporannya.
        abort_unless($laporan->isMilikPemilik($request->user()->id), 404);

        return $laporan->responFoto();
    }

    /** Milik pemilik ini, atau arsip pemilik sebelumnya (hanya-baca). */
    private function bolehDilihat(Request $request, LaporanKerusakan $laporan): bool
    {
        return $laporan->pemilik_id === null || $laporan->isMilikPemilik($request->user()->id);
    }
}
