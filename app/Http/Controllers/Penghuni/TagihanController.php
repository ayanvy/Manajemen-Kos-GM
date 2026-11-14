<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Penghuni hanya bisa MELIHAT tagihan miliknya sendiri. */
class TagihanController extends Controller
{
    public function index(Request $request): View
    {
        $tagihan = Tagihan::where('penghuni_id', $request->user()->id)
            ->orderByDesc('jatuh_tempo')
            ->orderByDesc('id')
            ->get();

        return view('penghuni.tagihan.index', compact('tagihan'));
    }

    public function show(Request $request, Tagihan $tagihan): View
    {
        abort_unless($tagihan->penghuni_id === $request->user()->id, 404);

        $tagihan->load('penghuni.kamar');

        return view('penghuni.tagihan.show', compact('tagihan'));
    }
}
