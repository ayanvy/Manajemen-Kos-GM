<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\PeraturanKos;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Penghuni hanya bisa MELIHAT peraturan dari pemilik kamar yang ia tempati. */
class PeraturanKosController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $peraturan = PeraturanKos::untukPenghuni($user)->get();
        $punyaKamar = $user->kamar()->exists();

        return view('penghuni.peraturan.index', compact('peraturan', 'punyaKamar'));
    }
}
