<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Tagihan::with([
            'pembayaran' => function ($query) {
                $query->select([
                    'id',
                    'tagihan_id',
                    'metode',
                    'tgl_bayar',
                    'jumlah',
                    'status',
                    'alasan_penolakan',
                    'tgl_verifikasi',
                ])->latest();
            },
        ]);

        if ($user->isPemilik()) {
            $query->tampilUntukPemilik($user->id);
        } elseif ($user->isPenghuni()) {
            $query->where('penghuni_id', $user->id);
        } else {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $tagihan = $query->latest()->get();

        return response()->json([
            'message' => 'Data tagihan berhasil diambil.',
            'data' => $tagihan,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $query = Tagihan::with([
            'pembayaran' => function ($query) {
                $query->select([
                    'id',
                    'tagihan_id',
                    'metode',
                    'tgl_bayar',
                    'jumlah',
                    'status',
                    'alasan_penolakan',
                    'tgl_verifikasi',
                ])->latest();
            },
        ])->whereKey($id);

        if ($user->isPemilik()) {
            $query->tampilUntukPemilik($user->id);
        } elseif ($user->isPenghuni()) {
            $query->where('penghuni_id', $user->id);
        } else {
            return response()->json([
                'message' => 'Anda tidak memiliki akses.',
            ], 403);
        }

        $tagihan = $query->first();

        if (!$tagihan) {
            return response()->json([
                'message' => 'Data tagihan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail tagihan berhasil diambil.',
            'data' => $tagihan,
        ]);
    }
}
