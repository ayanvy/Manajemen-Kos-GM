<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagihanRequest;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagihanController extends Controller
{
    public function index(Request $request): View
    {
        // Tagihan milik pemilik saat ini + arsip pemilik sebelumnya (pemilik_id null).
        $tagihan = Tagihan::tampilUntukPemilik($request->user()->id)
            ->with('penghuni.kamar')
            ->orderByDesc('jatuh_tempo')
            ->orderByDesc('id')
            ->get();

        return view('pemilik.tagihan.index', compact('tagihan'));
    }

    /** Detail tagihan, termasuk arsip (akun penghuni sudah dihapus). Hanya-baca. */
    public function show(Request $request, Tagihan $tagihan): View
    {
        $pemilikId = $request->user()->id;

        abort_unless($tagihan->pemilik_id === null || $tagihan->isMilikPemilik($pemilikId), 404);

        $tagihan->load(['penghuni.kamar', 'pembayaran' => fn ($q) => $q->with('verifikator')->latest('tgl_bayar')->latest('id')]);

        return view('pemilik.tagihan.show', compact('tagihan'));
    }

    public function create(Request $request): View
    {
        $penghuniOptions = $this->daftarPenghuni($request);

        return view('pemilik.tagihan.create', compact('penghuniOptions'));
    }

    public function store(TagihanRequest $request): RedirectResponse
    {
        $data = $request->dataTagihan();

        $tagihan = new Tagihan($data);
        $tagihan->pemilik_id = $request->user()->id;

        // Snapshot nama: tetap tersimpan walau akun dihapus nanti.
        $tagihan->nama_pemilik = $request->user()->name;
        $tagihan->nama_penghuni = User::findOrFail($data['penghuni_id'])->name;
        $tagihan->save();

        return redirect()->route('pemilik.tagihan.index')
            ->with('status', 'tagihan-created');
    }

    public function edit(Request $request, Tagihan $tagihan): View
    {
        $this->pastikanMilik($request, $tagihan);

        $penghuniOptions = $this->daftarPenghuni($request, $tagihan);

        return view('pemilik.tagihan.edit', compact('tagihan', 'penghuniOptions'));
    }

    public function update(TagihanRequest $request, Tagihan $tagihan): RedirectResponse
    {
        $this->pastikanMilik($request, $tagihan);

        $data = $request->dataTagihan();

        // Snapshot nama penghuni hanya diperbarui kalau penghuninya diganti.
        // Snapshot nama pemilik tidak disentuh: itu nama pemilik yang menerbitkan.
        if ((int) $data['penghuni_id'] !== (int) $tagihan->penghuni_id) {
            $tagihan->nama_penghuni = User::findOrFail($data['penghuni_id'])->name;
        }

        $tagihan->fill($data)->save();

        return redirect()->route('pemilik.tagihan.index')
            ->with('status', 'tagihan-updated');
    }

    public function destroy(Request $request, Tagihan $tagihan): RedirectResponse
    {
        $this->pastikanMilik($request, $tagihan);

        $tagihan->delete();

        return redirect()->route('pemilik.tagihan.index')
            ->with('status', 'tagihan-deleted');
    }

    /** Penghuni yang menempati kamar milik pemilik ini (beserta kamarnya, untuk harga default). */
    private function daftarPenghuni(Request $request, ?Tagihan $tagihan = null)
    {
        $pemilikId = $request->user()->id;

        return User::where('role', User::ROLE_PENGHUNI)
            ->where(function ($query) use ($pemilikId, $tagihan) {
                $query->whereHas('kamar', fn ($q) => $q->where('pemilik_id', $pemilikId));

                if ($tagihan !== null) {
                    $query->orWhere('id', $tagihan->penghuni_id);
                }
            })
            ->with(['kamar' => fn ($q) => $q->where('pemilik_id', $pemilikId)])
            ->orderBy('name')
            ->get();
    }

    /**
     * Hanya tagihan aktif milik akun yang login yang boleh diubah/dihapus.
     * Arsip (penghuni atau pemilik sudah dihapus) dan tagihan pemilik lain ditolak di sini,
     * walau arsip tampil di daftar. Dicek di server, bukan hanya dengan menyembunyikan tombol.
     */
    private function pastikanMilik(Request $request, Tagihan $tagihan): void
    {
        abort_unless($tagihan->dapatDikelolaOleh($request->user()->id), 404);
    }
}
