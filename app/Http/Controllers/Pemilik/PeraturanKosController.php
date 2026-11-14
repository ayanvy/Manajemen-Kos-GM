<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeraturanKosRequest;
use App\Models\PeraturanKos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeraturanKosController extends Controller
{
    public function index(Request $request): View
    {
        $peraturan = PeraturanKos::milikPemilik($request->user()->id)->get();

        return view('pemilik.peraturan.index', compact('peraturan'));
    }

    public function create(): View
    {
        return view('pemilik.peraturan.create');
    }

    public function store(PeraturanKosRequest $request): RedirectResponse
    {
        $peraturan = new PeraturanKos($request->validated());
        $peraturan->pemilik_id = $request->user()->id;
        $peraturan->save();

        return redirect()->route('pemilik.peraturan.index')
            ->with('status', 'peraturan-created');
    }

    public function edit(Request $request, PeraturanKos $peraturan): View
    {
        $this->pastikanMilik($request, $peraturan);

        return view('pemilik.peraturan.edit', compact('peraturan'));
    }

    public function update(PeraturanKosRequest $request, PeraturanKos $peraturan): RedirectResponse
    {
        $this->pastikanMilik($request, $peraturan);

        $peraturan->update($request->validated());

        return redirect()->route('pemilik.peraturan.index')
            ->with('status', 'peraturan-updated');
    }

    public function destroy(Request $request, PeraturanKos $peraturan): RedirectResponse
    {
        $this->pastikanMilik($request, $peraturan);

        $peraturan->delete();

        return redirect()->route('pemilik.peraturan.index')
            ->with('status', 'peraturan-deleted');
    }

    /** Pemilik hanya boleh menyentuh peraturan miliknya sendiri. */
    private function pastikanMilik(Request $request, PeraturanKos $peraturan): void
    {
        abort_unless($peraturan->pemilik_id === $request->user()->id, 404);
    }
}
