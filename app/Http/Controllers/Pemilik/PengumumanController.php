<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengumumanRequest;
use App\Models\Pengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    public function index(Request $request): View
    {
        $pengumuman = Pengumuman::milikPemilik($request->user()->id)->get();

        return view('pemilik.pengumuman.index', compact('pengumuman'));
    }

    public function create(): View
    {
        return view('pemilik.pengumuman.create');
    }

    public function store(PengumumanRequest $request): RedirectResponse
    {
        $pengumuman = new Pengumuman($request->validated());
        $pengumuman->pemilik_id = $request->user()->id;
        $pengumuman->tgl_post = now();
        $pengumuman->save();

        return redirect()->route('pemilik.pengumuman.index')
            ->with('status', 'pengumuman-created');
    }

    public function show(Request $request, Pengumuman $pengumuman): View
    {
        $this->pastikanMilik($request, $pengumuman);

        return view('pemilik.pengumuman.show', compact('pengumuman'));
    }

    public function edit(Request $request, Pengumuman $pengumuman): View
    {
        $this->pastikanMilik($request, $pengumuman);

        return view('pemilik.pengumuman.edit', compact('pengumuman'));
    }

    public function update(PengumumanRequest $request, Pengumuman $pengumuman): RedirectResponse
    {
        $this->pastikanMilik($request, $pengumuman);

        $pengumuman->update($request->validated());

        return redirect()->route('pemilik.pengumuman.index')
            ->with('status', 'pengumuman-updated');
    }

    public function destroy(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $this->pastikanMilik($request, $pengumuman);

        $pengumuman->delete();

        return redirect()->route('pemilik.pengumuman.index')
            ->with('status', 'pengumuman-deleted');
    }

    /** Pemilik hanya boleh menyentuh pengumuman miliknya sendiri. */
    private function pastikanMilik(Request $request, Pengumuman $pengumuman): void
    {
        abort_unless($pengumuman->pemilik_id === $request->user()->id, 404);
    }
}
