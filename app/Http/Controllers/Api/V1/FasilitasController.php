<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FasilitasController extends Controller
{
    /** Daftar semua fasilitas */
    public function index(): JsonResponse
    {
        $data = DB::table('fasilitas')->orderBy('nama')->get();

        return response()->json(['data' => $data]);
    }

    /** Tambah fasilitas baru */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:fasilitas,nama'],
        ]);

        $id = DB::table('fasilitas')->insertGetId([
            'nama'       => $data['nama'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'data'    => ['id' => $id, 'nama' => $data['nama']],
            'message' => 'Fasilitas berhasil ditambahkan.',
        ], 201);
    }

    /** Hapus fasilitas */
    public function destroy(int $id): Response
    {
        DB::table('fasilitas')->where('id', $id)->delete();

        return response()->noContent();
    }
}
