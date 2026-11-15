<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Http\Requests\LaporanKerusakanRequest;
use App\Models\LaporanKerusakan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use App\Services\CloudinaryService;

/** Penghuni membuat, mengubah, menghapus, dan memantau laporan kerusakan miliknya sendiri. */
class LaporanKerusakanController extends Controller
{
    public function index(Request $request): View
    {
        $laporan = $this->milikPenghuni($request)
            ->orderByDesc('tgl_lapor')
            ->orderByDesc('id')
            ->get();

        $kamar = $request->user()->kamar;

        return view('penghuni.laporan.index', compact('laporan', 'kamar'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $kamar = $request->user()->kamar;

        if ($kamar === null) {
            return redirect()->route('penghuni.laporan.index')
                ->with('error', 'Anda belum menempati kamar, jadi belum bisa membuat laporan kerusakan.');
        }

        return view('penghuni.laporan.create', compact('kamar'));
    }

    public function store(
        LaporanKerusakanRequest $request,
        CloudinaryService $cloudinary
    ): RedirectResponse {
        $user = $request->user();
        $kamar = $user->kamar;

        if ($kamar === null) {
            return redirect()->route('penghuni.laporan.index')
                ->with('error', 'Anda belum menempati kamar, jadi belum bisa membuat laporan kerusakan.');
        }

        $publicIdBaru = null;

        try {
            $laporan = new LaporanKerusakan([
                'deskripsi' => $request->validated('deskripsi'),
            ]);

            if ($request->hasFile('foto')) {
                $result = $cloudinary->uploadImage(
                    $request->file('foto')->getRealPath(),
                    'sistem-informasi-kos/laporan',
                    'authenticated'
                );

                $publicIdBaru = $result['public_id'];
                $laporan->foto = $result['secure_url'];
                $laporan->foto_public_id = $publicIdBaru;
            }

            $laporan->penghuni_id = $user->id;
            $laporan->pemilik_id = $kamar->pemilik_id;
            $laporan->kamar_id = $kamar->id;
            $laporan->nama_penghuni = $user->name;
            $laporan->nomor_kamar = $kamar->nomor_kamar;
            $laporan->tgl_lapor = now()->toDateString();
            $laporan->status = LaporanKerusakan::STATUS_DIKIRIM;
            $laporan->save();
        } catch (Throwable $e) {
            $this->bersihkanFotoYatim(null, $publicIdBaru, $cloudinary);
            throw $e;
        }

        return redirect()->route('penghuni.laporan.index')
            ->with('status', 'laporan-created');
    }

    /** Status terbaru semua laporan penghuni, lengkap dengan tahapannya. */
    public function status(Request $request): View
    {
        $laporan = $this->milikPenghuni($request)
            ->orderByDesc('tgl_lapor')
            ->orderByDesc('id')
            ->get();

        return view('penghuni.laporan.status', compact('laporan'));
    }

    public function show(Request $request, LaporanKerusakan $laporan): View
    {
        $this->pastikanMilik($request, $laporan);

        return view('penghuni.laporan.show', compact('laporan'));
    }

    public function edit(Request $request, LaporanKerusakan $laporan): View|RedirectResponse
    {
        $this->pastikanMilik($request, $laporan);

        if (! $laporan->bisaDiubahPenghuni()) {
            return $this->tidakBisaDiubah($laporan);
        }

        return view('penghuni.laporan.edit', compact('laporan'));
    }

    public function update(
        LaporanKerusakanRequest $request,
        LaporanKerusakan $laporan,
        CloudinaryService $cloudinary
    ): RedirectResponse {
        $this->pastikanMilik($request, $laporan);

        $userId = $request->user()->id;
        $publicIdBaru = null;

        try {
            return DB::transaction(function () use (
                $request,
                $laporan,
                $userId,
                $cloudinary,
                &$publicIdBaru
            ) {
                $terkunci = LaporanKerusakan::whereKey($laporan->getKey())
                    ->where('penghuni_id', $userId)
                    ->lockForUpdate()
                    ->first();

                abort_if($terkunci === null, 404);

                if (! $terkunci->bisaDiubahPenghuni()) {
                    return $this->tidakBisaDiubah($terkunci);
                }

                $terkunci->deskripsi = $request->validated('deskripsi');

                $pathLama = null;
                $publicIdLama = null;

                if ($request->hasFile('foto')) {
                    $result = $cloudinary->uploadImage(
                        $request->file('foto')->getRealPath(),
                        'sistem-informasi-kos/laporan',
                        'authenticated'
                    );

                    $publicIdBaru = $result['public_id'];
                    $pathLama = $terkunci->foto;
                    $publicIdLama = $terkunci->foto_public_id;

                    $terkunci->foto = $result['secure_url'];
                    $terkunci->foto_public_id = $publicIdBaru;
                } elseif ($request->boolean('hapus_foto') && $terkunci->foto) {
                    $pathLama = $terkunci->foto;
                    $publicIdLama = $terkunci->foto_public_id;

                    $terkunci->foto = null;
                    $terkunci->foto_public_id = null;
                }

                $terkunci->save();

                if ($pathLama) {
                    DB::afterCommit(function () use (
                        $pathLama,
                        $publicIdLama,
                        $cloudinary
                    ) {
                        try {
                            if ($publicIdLama) {
                                $cloudinary->deleteImage(
                                    $publicIdLama,
                                    'authenticated'
                                );
                            } elseif (! str_starts_with($pathLama, 'http')) {
                                Storage::disk('local')->delete($pathLama);
                            }
                        } catch (Throwable $e) {
                            report($e);
                        }
                    });
                }

                return redirect()->route('penghuni.laporan.show', $terkunci)
                    ->with('status', 'laporan-updated');
            });
        } catch (Throwable $e) {
            $this->bersihkanFotoYatim(null, $publicIdBaru, $cloudinary);
            throw $e;
        }
    }

    public function destroy(Request $request, LaporanKerusakan $laporan): RedirectResponse
    {
        $this->pastikanMilik($request, $laporan);

        $userId = $request->user()->id;

        return DB::transaction(function () use ($laporan, $userId) {
            $terkunci = LaporanKerusakan::whereKey($laporan->getKey())
                ->where('penghuni_id', $userId)
                ->lockForUpdate()
                ->first();

            abort_if($terkunci === null, 404);

            if (! $terkunci->bisaDiubahPenghuni()) {
                return $this->tidakBisaDiubah($terkunci);
            }

            $terkunci->delete(); // file foto dihapus oleh event deleted pada model

            return redirect()->route('penghuni.laporan.index')
                ->with('status', 'laporan-deleted');
        });
    }

    public function foto(Request $request, LaporanKerusakan $laporan): Response
    {
        $this->pastikanMilik($request, $laporan);

        return $laporan->responFoto();
    }

    private function milikPenghuni(Request $request)
    {
        return LaporanKerusakan::where('penghuni_id', $request->user()->id);
    }

    private function pastikanMilik(Request $request, LaporanKerusakan $laporan): void
    {
        abort_unless($laporan->isMilikPenghuni((int) $request->user()->id), 404);
    }

    private function tidakBisaDiubah(LaporanKerusakan $laporan): RedirectResponse
    {
        return redirect()->route('penghuni.laporan.show', $laporan)
            ->with('error', 'Laporan ini sudah diproses pemilik, jadi tidak bisa diubah atau dihapus lagi.');
    }

    /**
     * Hapus file foto yang tidak punya baris laporan. Dipanggil saat penyimpanan gagal.
     * Kalau baris dengan path ini ternyata sudah tersimpan, atau pengecekannya gagal,
     * file dibiarkan: lebih aman meninggalkan satu file yatim daripada menghapus foto yang tercatat.
     */
    private function bersihkanFotoYatim(
        ?string $path,
        ?string $publicId = null,
        ?CloudinaryService $cloudinary = null
    ): void {
        try {
            if ($publicId !== null) {
                if (LaporanKerusakan::where('foto_public_id', $publicId)->exists()) {
                    return;
                }

                $cloudinary?->deleteImage($publicId, 'authenticated');

                return;
            }

            if ($path === null) {
                return;
            }

            if (LaporanKerusakan::where('foto', $path)->exists()) {
                return;
            }

            Storage::disk('local')->delete($path);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
