<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/** Penghuni membayar tagihan, memantau status, dan melihat riwayat pembayaran miliknya sendiri. */
class PembayaranController extends Controller
{
    /** Form pembayaran. Tagihan dipilih lewat ?tagihan=ID, atau otomatis tagihan tertua yang belum dibayar. */
    public function create(Request $request): View|RedirectResponse
    {
        $bisaDibayar = $this->tagihanBisaDibayar($request);

        if ($request->filled('tagihan')) {
            $tagihan = $bisaDibayar->firstWhere('id', (int) $request->query('tagihan'));

            if ($tagihan === null) {
                return redirect()->route('penghuni.tagihan.index')
                    ->with('error', 'Tagihan tersebut tidak bisa dibayar. Mungkin sudah lunas atau pembayarannya sedang diverifikasi.');
            }
        } else {
            $tagihan = $bisaDibayar->first();
        }

        $pembayaranDitolak = $tagihan
            ? $tagihan->pembayaran()->where('status', Pembayaran::STATUS_DITOLAK)->latest('id')->first()
            : null;

        return view('penghuni.pembayaran.create', [
            'tagihan' => $tagihan,
            'tagihanLain' => $bisaDibayar->where('id', '!=', $tagihan?->id),
            'pembayaranDitolak' => $pembayaranDitolak,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tagihan_id' => ['required', 'integer'],
            'metode' => ['required', Rule::in(array_keys(Pembayaran::METODE))],
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:3072'],
        ], [
            'metode.required' => 'Pilih metode pembayaran.',
            'metode.in' => 'Metode pembayaran tidak valid.',
            'bukti.required' => 'Unggah bukti pembayaran.',
            'bukti.file' => 'Bukti pembayaran harus berupa file.',
            'bukti.mimes' => 'Bukti pembayaran harus berformat JPG, PNG, atau PDF.',
            'bukti.max' => 'Ukuran bukti pembayaran maksimal 3 MB.',
        ]);

        $userId = $request->user()->id;

        // Path file yang sudah ditulis ke disk. Diisi di dalam transaksi, dibersihkan di catch
        // kalau transaksi gagal (termasuk saat commit), sehingga tidak ada file yatim.
        $path = null;

        try {
            return DB::transaction(function () use ($request, $data, $userId, &$path) {
                // Kunci baris tagihan supaya dua kiriman bersamaan tidak lolos dua-duanya.
                $tagihan = Tagihan::where('id', $data['tagihan_id'])
                    ->where('penghuni_id', $userId)
                    ->lockForUpdate()
                    ->first();

                abort_if($tagihan === null, 404);

                if ($tagihan->isLunas()) {
                    return back()->withInput()->withErrors(['tagihan_id' => 'Tagihan ini sudah lunas.']);
                }

                if ($tagihan->sedangDiverifikasi()) {
                    return back()->withInput()->withErrors(['tagihan_id' => 'Pembayaran untuk tagihan ini sedang menunggu verifikasi.']);
                }

                $disimpan = $request->file('bukti')->store('pembayaran/bukti', 'local');

                if ($disimpan === false) {
                    throw new RuntimeException('Gagal menyimpan file bukti pembayaran.');
                }

                $path = $disimpan;

                $pembayaran = new Pembayaran([
                    'tagihan_id' => $tagihan->id,
                    'metode' => $data['metode'],
                    'tgl_bayar' => now()->toDateString(),
                    'jumlah' => $tagihan->jumlah, // selalu ikut tagihan, bukan dari input
                    'bukti_pembayaran' => $path,
                ]);
                $pembayaran->status = Pembayaran::STATUS_MENUNGGU;
                $pembayaran->save();

                return redirect()->route('penghuni.pembayaran.status')
                    ->with('status', 'pembayaran-sent');
            });
        } catch (Throwable $e) {
            $this->bersihkanBuktiYatim($path);

            throw $e;
        }
    }

    /** Status terbaru pembayaran untuk tiap tagihan. */
    public function status(Request $request): View
    {
        $pembayaran = $this->milikPenghuni($request)
            ->with('tagihan')
            ->latest('id')
            ->get()
            ->unique('tagihan_id')
            ->values();

        return view('penghuni.pembayaran.status', compact('pembayaran'));
    }

    public function riwayat(Request $request): View
    {
        $pembayaran = $this->milikPenghuni($request)
            ->with('tagihan')
            ->orderByDesc('tgl_bayar')
            ->orderByDesc('id')
            ->get();

        return view('penghuni.pembayaran.riwayat', compact('pembayaran'));
    }

    public function show(Request $request, Pembayaran $pembayaran): View
    {
        $this->pastikanMilik($request, $pembayaran);

        $pembayaran->load('tagihan', 'verifikator');

        return view('penghuni.pembayaran.show', compact('pembayaran'));
    }

    public function bukti(Request $request, Pembayaran $pembayaran): Response
    {
        $this->pastikanMilik($request, $pembayaran);

        return $pembayaran->responBukti($request->boolean('unduh'));
    }

    /** Tagihan milik penghuni yang belum lunas dan tidak sedang menunggu verifikasi. */
    private function tagihanBisaDibayar(Request $request)
    {
        return Tagihan::where('penghuni_id', $request->user()->id)
            ->where('status', Tagihan::STATUS_BELUM_DIBAYAR)
            ->whereDoesntHave('pembayaran', fn ($q) => $q->where('status', Pembayaran::STATUS_MENUNGGU))
            ->orderBy('jatuh_tempo')
            ->orderBy('id')
            ->get();
    }

    private function milikPenghuni(Request $request)
    {
        return Pembayaran::whereHas('tagihan', fn ($q) => $q->where('penghuni_id', $request->user()->id));
    }

    private function pastikanMilik(Request $request, Pembayaran $pembayaran): void
    {
        $pembayaran->loadMissing('tagihan');

        // Bandingkan sebagai integer: ID dari database bisa berupa string tergantung driver/konfigurasi.
        $penghuniId = $pembayaran->tagihan?->penghuni_id;

        abort_unless(
            $penghuniId !== null && (int) $penghuniId === (int) $request->user()->id,
            404
        );
    }

    /**
     * Hapus file bukti yang tidak punya baris pembayaran. Dipanggil saat transaksi gagal.
     *
     * Kalau baris dengan path ini ternyata sudah tersimpan (mis. koneksi putus tepat setelah
     * commit berhasil), file dibiarkan. Kalau pengecekan itu sendiri gagal, file juga dibiarkan:
     * lebih aman meninggalkan satu file yatim daripada menghapus bukti yang sudah tercatat.
     */
    private function bersihkanBuktiYatim(?string $path): void
    {
        if ($path === null) {
            return;
        }

        try {
            if (Pembayaran::where('bukti_pembayaran', $path)->exists()) {
                return;
            }

            Storage::disk('local')->delete($path);
        } catch (Throwable $e) {
            report($e);
        }
    }
}