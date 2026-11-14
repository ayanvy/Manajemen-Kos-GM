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
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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

    public function store(LaporanKerusakanRequest $request): RedirectResponse
    {
        $user = $request->user();
        $kamar = $user->kamar;

        if ($kamar === null) {
            return redirect()->route('penghuni.laporan.index')
                ->with('error', 'Anda belum menempati kamar, jadi belum bisa membuat laporan kerusakan.');
        }

        // Path file yang sudah ditulis ke disk; dibersihkan di catch kalau penyimpanan gagal.
        $path = null;

        try {
            $laporan = new LaporanKerusakan(['deskripsi' => $request->validated('deskripsi')]);

            if ($request->hasFile('foto')) {
                $disimpan = $request->file('foto')->store('laporan/foto', 'local');

                if ($disimpan === false) {
                    throw new RuntimeException('Gagal menyimpan file foto laporan.');
                }

                $path = $disimpan;
                $laporan->foto = $path;
            }

            // Penghuni, pemilik, dan kamar selalu diambil dari data kamar penghuni, bukan dari input.
            $laporan->penghuni_id = $user->id;
            $laporan->pemilik_id = $kamar->pemilik_id;
            $laporan->kamar_id = $kamar->id;
            $laporan->nama_penghuni = $user->name;
            $laporan->nomor_kamar = $kamar->nomor_kamar;
            $laporan->tgl_lapor = now()->toDateString();
            $laporan->status = LaporanKerusakan::STATUS_DIKIRIM;
            $laporan->save();
        } catch (Throwable $e) {
            $this->bersihkanFotoYatim($path);

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

    public function update(LaporanKerusakanRequest $request, LaporanKerusakan $laporan): RedirectResponse
    {
        $this->pastikanMilik($request, $laporan);

        $userId = $request->user()->id;
        $pathBaru = null;

        try {
            return DB::transaction(function () use ($request, $laporan, $userId, &$pathBaru) {
                // Kunci baris supaya tidak bentrok dengan pemilik yang sedang mengubah status.
                $terkunci = LaporanKerusakan::whereKey($laporan->getKey())
                    ->where('penghuni_id', $userId)
                    ->lockForUpdate()
                    ->first();

                abort_if($terkunci === null, 404);

                // Status dicek ulang setelah terkunci: pemilik mungkin sudah memprosesnya.
                if (! $terkunci->bisaDiubahPenghuni()) {
                    return $this->tidakBisaDiubah($terkunci);
                }

                $terkunci->deskripsi = $request->validated('deskripsi');
                $pathLama = null;

                if ($request->hasFile('foto')) {
                    $disimpan = $request->file('foto')->store('laporan/foto', 'local');

                    if ($disimpan === false) {
                        throw new RuntimeException('Gagal menyimpan file foto laporan.');
                    }

                    $pathBaru = $disimpan;
                    $pathLama = $terkunci->foto;
                    $terkunci->foto = $pathBaru;
                } elseif ($request->boolean('hapus_foto') && $terkunci->foto) {
                    $pathLama = $terkunci->foto;
                    $terkunci->foto = null;
                }

                $terkunci->save();

                // File lama baru dihapus setelah commit, supaya rollback tidak menghilangkan foto.
                if ($pathLama) {
                    DB::afterCommit(fn () => Storage::disk('local')->delete($pathLama));
                }

                return redirect()->route('penghuni.laporan.show', $terkunci)
                    ->with('status', 'laporan-updated');
            });
        } catch (Throwable $e) {
            $this->bersihkanFotoYatim($pathBaru);

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
    private function bersihkanFotoYatim(?string $path): void
    {
        if ($path === null) {
            return;
        }

        try {
            if (LaporanKerusakan::where('foto', $path)->exists()) {
                return;
            }

            Storage::disk('local')->delete($path);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
