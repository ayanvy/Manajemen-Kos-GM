<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Penghuni hanya bisa MELIHAT pengumuman dari pemilik kamar yang ia tempati. */
class PengumumanController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $pengumuman = Pengumuman::untukPenghuni($user)->get();
        $punyaKamar = $user->kamar()->exists();

        return view('penghuni.pengumuman.index', compact('pengumuman', 'punyaKamar'));
    }

    public function show(Request $request, Pengumuman $pengumuman): View
    {
        $kamar = $request->user()->kamar;

        abort_unless($kamar && $pengumuman->pemilik_id === $kamar->pemilik_id, 404);

        return view('penghuni.pengumuman.show', compact('pengumuman'));
    }
}
