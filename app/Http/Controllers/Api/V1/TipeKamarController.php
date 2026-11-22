<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TipeKamarResource;
use App\Models\TipeKamar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TipeKamarController extends Controller
{
    /** Daftar tipe kamar (untuk dropdown & landing page) */
    public function index(): AnonymousResourceCollection
    {
        return TipeKamarResource::collection(
            TipeKamar::query()->orderBy('nama_tipe')->get()
        );
    }

    /** Tambah tipe kamar baru (admin) */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama_tipe'       => ['required', 'string', 'max:100', 'unique:tipe_kamar,nama_tipe'],
            'kapasitas'       => ['required', 'integer', 'min:1'],
            'harga_per_malam' => ['required', 'numeric', 'min:0'],
            'deskripsi'       => ['nullable', 'string'],
        ]);

        $tipe = TipeKamar::create($data);

        return (new TipeKamarResource($tipe))->response()->setStatusCode(201);
    }

    /** Ubah tipe kamar (admin) */
    public function update(Request $request, TipeKamar $tipeKamar): TipeKamarResource
    {
        $data = $request->validate([
            'nama_tipe'       => ['sometimes', 'string', 'max:100', 'unique:tipe_kamar,nama_tipe,' . $tipeKamar->id],
            'kapasitas'       => ['sometimes', 'integer', 'min:1'],
            'harga_per_malam' => ['sometimes', 'numeric', 'min:0'],
            'deskripsi'       => ['nullable', 'string'],
        ]);

        $tipeKamar->update($data);

        return new TipeKamarResource($tipeKamar);
    }

    /** Hapus tipe kamar — hanya jika tidak ada kamar yang menggunakannya (admin) */
    public function destroy(TipeKamar $tipeKamar): Response|JsonResponse
    {
        if ($tipeKamar->kamar()->exists()) {
            return response()->json([
                'message' => 'Tipe kamar masih digunakan oleh kamar lain dan tidak dapat dihapus.',
            ], 409);
        }

        $tipeKamar->delete();

        return response()->noContent();
    }
}
