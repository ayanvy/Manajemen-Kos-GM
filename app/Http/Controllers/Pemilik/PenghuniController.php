<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pemilik hanya bisa MELIHAT dan MENGHAPUS penghuni.
 * Penghuni dibuat lewat halaman register, jadi tidak ada create/store/edit/update di sini.
 */
class PenghuniController extends Controller
{
    public function index(Request $request): View
    {
        $pemilikId = $request->user()->id;

        $penghuni = User::where('role', User::ROLE_PENGHUNI)
            // Tampilkan kamar hanya kalau kamar itu milik pemilik yang sedang login.
            ->with(['kamar' => fn ($query) => $query->where('pemilik_id', $pemilikId)])
            ->latest()
            ->get();

        return view('pemilik.penghuni.index', compact('penghuni'));
    }

    public function destroy(User $penghuni): RedirectResponse
    {
        // Route ini hanya boleh menghapus penghuni, bukan pemilik.
        abort_unless($penghuni->isPenghuni(), 404);

        // Kamar dikosongkan, foto dibersihkan, dan sesi login ditutup lewat hook deleting di model User.
        $penghuni->delete();

        return redirect()->route('pemilik.penghuni.index')
            ->with('status', 'penghuni-deleted');
    }
}
