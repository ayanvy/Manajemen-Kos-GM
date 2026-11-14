<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/** Pemilik memverifikasi (terima/tolak) pembayaran penghuni dan melihat bukti. */
class PembayaranController extends Controller
{
    /** Jumlah percobaan transaksi (percobaan ulang hanya terjadi saat deadlock). */
    private const PERCOBAAN_TRANSAKSI = 3;

    public function index(Request $request): View
    {
        $pembayaran = Pembayaran::perluDiverifikasi($request->user()->id)
            ->with('tagihan.penghuni.kamar')
            ->oldest('id')
            ->get();

        return view('pemilik.pembayaran.index', compact('pembayaran'));
    }

    public function terima(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        return DB::transaction(function () use ($request, $pembayaran) {
            [$p, $tagihan] = $this->kunciUntukVerifikasi($request, $pembayaran);

            // Sudah diterima/ditolak oleh permintaan lain: jangan diproses lagi.
            if (! $p->isMenunggu()) {
                return $this->sudahDiproses();
            }

            // Tagihan sudah lunas (mis. pembayaran lain untuk tagihan ini sudah diterima): jangan terima lagi.
            if ($tagihan->isLunas()) {
                return redirect()->route('pemilik.pembayaran.index')
                    ->with('error', 'Tagihan ini sudah lunas, jadi pembayaran tidak bisa diterima lagi.');
            }

            $p->status = Pembayaran::STATUS_DITERIMA;
            $p->alasan_penolakan = null;
            $p->diverifikasi_oleh = $request->user()->id;
            $p->tgl_verifikasi = now();
            $p->save();

            // Pembayaran diterima = tagihan otomatis lunas (transaksi yang sama).
            $tagihan->update(['status' => Tagihan::STATUS_LUNAS]);

            return redirect()->route('pemilik.pembayaran.index')
                ->with('status', 'pembayaran-accepted');
        }, self::PERCOBAAN_TRANSAKSI);
    }

    public function tolak(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        $data = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:500'],
        ], [
            'alasan_penolakan.required' => 'Tuliskan alasan penolakan agar penghuni tahu apa yang perlu diperbaiki.',
            'alasan_penolakan.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        return DB::transaction(function () use ($request, $pembayaran, $data) {
            [$p] = $this->kunciUntukVerifikasi($request, $pembayaran);

            if (! $p->isMenunggu()) {
                return $this->sudahDiproses();
            }

            
            $p->status = Pembayaran::STATUS_DITOLAK;
            $p->alasan_penolakan = $data['alasan_penolakan'];
            $p->diverifikasi_oleh = $request->user()->id;
            $p->tgl_verifikasi = now();
            $p->save();

            return redirect()->route('pemilik.pembayaran.index')
                ->with('status', 'pembayaran-rejected');
        }, self::PERCOBAAN_TRANSAKSI);
    }

    public function bukti(Request $request, Pembayaran $pembayaran): Response
    {
        $pembayaran->loadMissing('tagihan');

        abort_unless($pembayaran->tagihan?->isMilikPemilik($request->user()->id), 404);

        return $pembayaran->responBukti($request->boolean('unduh'));
    }

    /**
     * Harus dipanggil di dalam DB::transaction.
     *
     * Urutan kunci: baris tagihan dulu, baru baris pembayaran. Urutan ini sama dengan proses
     * penghuni mengirim pembayaran (tagihan dikunci sebelum pembayaran baru ditambahkan),
     * sehingga kedua alur tidak saling menunggu secara melingkar.
     *
     * tagihan_id pada pembayaran tidak pernah berubah, jadi aman dibaca tanpa kunci hanya
     * untuk menemukan tagihan yang harus dikunci lebih dulu. Setelah terkunci, pembayaran
     * dibaca ulang dengan syarat tagihan_id cocok, lalu tagihan harus aktif (bukan arsip)
     * dan milik pemilik yang sedang login. Dicek di server, bukan hanya dengan
     * menyembunyikan tombol.
     *
     * @return array{0: Pembayaran, 1: Tagihan}
     */
    private function kunciUntukVerifikasi(Request $request, Pembayaran $pembayaran): array
    {
        $tagihanId = Pembayaran::whereKey($pembayaran->getKey())->value('tagihan_id');
        abort_if($tagihanId === null, 404);

        $tagihan = Tagihan::whereKey($tagihanId)->lockForUpdate()->first();
        abort_unless($tagihan?->dapatDikelolaOleh($request->user()->id), 404);

        $p = Pembayaran::whereKey($pembayaran->getKey())
            ->where('tagihan_id', $tagihan->id)
            ->lockForUpdate()
            ->first();
        abort_if($p === null, 404);

        return [$p, $tagihan];
    }

    private function sudahDiproses(): RedirectResponse
    {
        return redirect()->route('pemilik.pembayaran.index')
            ->with('error', 'Pembayaran ini sudah diproses sebelumnya.');
    }
}