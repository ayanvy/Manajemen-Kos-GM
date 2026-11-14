<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Http\Requests\KamarRequest;
use App\Models\Kamar;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KamarController extends Controller
{
    public function index(Request $request): View
    {
        $kamar = Kamar::where('pemilik_id', $request->user()->id)
            ->with('penghuni')
            ->orderBy('nomor_kamar')
            ->get();

        return view('pemilik.kamar.index', compact('kamar'));
    }

    public function create(): View
    {
        $penghuniOptions = $this->daftarPenghuni();

        return view('pemilik.kamar.create', compact('penghuniOptions'));
    }

    public function store(KamarRequest $request): RedirectResponse
    {
        // Foto ditangani terpisah; bukan kolom yang boleh diisi massal dari input.
        $kamar = new Kamar($request->safe()->except(['foto', 'hapus_foto']));
        $kamar->pemilik_id = $request->user()->id;

        if ($request->hasFile('foto')) {
            $kamar->foto = $request->file('foto')->store('kamar-photos', 'public');
        }

        $kamar->save();

        return redirect()->route('pemilik.kamar.index')
            ->with('status', 'kamar-created');
    }

    public function edit(Request $request, Kamar $kamar): View
    {
        $this->pastikanMilik($request, $kamar);

        $penghuniOptions = $this->daftarPenghuni($kamar);

        return view('pemilik.kamar.edit', compact('kamar', 'penghuniOptions'));
    }

    public function update(KamarRequest $request, Kamar $kamar): RedirectResponse
    {
        $this->pastikanMilik($request, $kamar);

        $fotoLama = $kamar->foto;

        $kamar->fill($request->safe()->except(['foto', 'hapus_foto']));

        if ($request->hasFile('foto')) {
            $kamar->foto = $request->file('foto')->store('kamar-photos', 'public');
        } elseif ($request->boolean('hapus_foto')) {
            $kamar->foto = null;
        }

        $kamar->save();

        // File lama dihapus setelah data tersimpan, supaya tidak hilang kalau penyimpanan gagal.
        if ($fotoLama && $fotoLama !== $kamar->foto) {
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()->route('pemilik.kamar.index')
            ->with('status', 'kamar-updated');
    }

    public function destroy(Request $request, Kamar $kamar): RedirectResponse
    {
        $this->pastikanMilik($request, $kamar);

        if ($kamar->isTerisi()) {
            return redirect()->route('pemilik.kamar.index')
                ->with('status', 'kamar-terisi');
        }

        $kamar->delete();

        return redirect()->route('pemilik.kamar.index')
            ->with('status', 'kamar-deleted');
    }

    private function daftarPenghuni(?Kamar $kamar = null)
    {
        return User::where('role', User::ROLE_PENGHUNI)
            ->where(function ($query) use ($kamar) {
                $query->whereDoesntHave('kamar');

                if ($kamar !== null && $kamar->penghuni_id !== null) {
                    $query->orWhere('id', $kamar->penghuni_id);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /** Pemilik hanya boleh menyentuh kamar miliknya sendiri. */
    private function pastikanMilik(Request $request, Kamar $kamar): void
    {
        abort_unless($kamar->pemilik_id === $request->user()->id, 404);
    }
}