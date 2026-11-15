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
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use App\Services\CloudinaryService;

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

    public function store(
        Request $request,
        CloudinaryService $cloudinary
    ): RedirectResponse {
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
        $publicIdBaru = null;

        try {
            return DB::transaction(function () use (
                $request,
                $data,
                $userId,
                $cloudinary,
                &$publicIdBaru
            ) {
                $tagihan = Tagihan::where('id', $data['tagihan_id'])
                    ->where('penghuni_id', $userId)
                    ->lockForUpdate()
                    ->first();

                abort_if($tagihan === null, 404);

                if ($tagihan->isLunas()) {
                    return back()->withInput()
                        ->withErrors(['tagihan_id' => 'Tagihan ini sudah lunas.']);
                }

                if ($tagihan->sedangDiverifikasi()) {
                    return back()->withInput()
                        ->withErrors(['tagihan_id' => 'Pembayaran untuk tagihan ini sedang menunggu verifikasi.']);
                }

                $result = $cloudinary->uploadImage(
                    $request->file('bukti')->getRealPath(),
                    'sistem-informasi-kos/pembayaran',
                    'authenticated'
                );

                $publicIdBaru = $result['public_id'];

                $pembayaran = new Pembayaran([
                    'tagihan_id' => $tagihan->id,
                    'metode' => $data['metode'],
                    'tgl_bayar' => now()->toDateString(),
                    'jumlah' => $tagihan->jumlah,
                    'bukti_pembayaran' => $result['secure_url'],
                    'bukti_public_id' => $publicIdBaru,
                ]);

                $pembayaran->status = Pembayaran::STATUS_MENUNGGU;
                $pembayaran->save();

                return redirect()->route('penghuni.pembayaran.status')
                    ->with('status', 'pembayaran-sent');
            });
        } catch (Throwable $e) {
            $this->bersihkanBuktiYatim($publicIdBaru, $cloudinary);

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

    private function bersihkanBuktiYatim(
        ?string $publicId,
        CloudinaryService $cloudinary
    ): void {
        if ($publicId === null) {
            return;
        }

        try {
            if (Pembayaran::where('bukti_public_id', $publicId)->exists()) {
                return;
            }

            $cloudinary->deleteImage($publicId, 'authenticated');
        } catch (Throwable $e) {
            report($e);
        }
    }
}