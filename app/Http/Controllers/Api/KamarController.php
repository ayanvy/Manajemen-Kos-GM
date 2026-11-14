<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KamarController extends Controller
{
    /**
     * Menampilkan daftar kamar sesuai role pengguna.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Kamar::with('penghuni:id,name');

        if ($user->isPemilik()) {
            $query->where('pemilik_id', $user->id);
        } elseif ($user->isPenghuni()) {
            $query->where('penghuni_id', $user->id);
        } else {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $kamar = $query
            ->orderBy('nomor_kamar')
            ->get();

        return response()->json([
            'message' => 'Data kamar berhasil diambil.',
            'data' => $kamar,
        ]);
    }

    /**
     * Menampilkan detail kamar sesuai hak akses pengguna.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $query = Kamar::with('penghuni:id,name')
            ->whereKey($id);

        if ($user->isPemilik()) {
            $query->where('pemilik_id', $user->id);
        } elseif ($user->isPenghuni()) {
            $query->where('penghuni_id', $user->id);
        } else {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $kamar = $query->first();

        if (!$kamar) {
            return response()->json([
                'message' => 'Data kamar tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail kamar berhasil diambil.',
            'data' => $kamar,
        ]);
    }
}