<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TipeKamarRequest;
use App\Http\Resources\TipeKamarResource;
use App\Models\TipeKamar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TipeKamarController extends Controller
{
    /**
     * Daftar tipe kamar beserta fasilitasnya (publik, untuk dropdown dan halaman depan).
     */
    public function index(): AnonymousResourceCollection
    {
        return TipeKamarResource::collection(
            TipeKamar::query()->with(['fasilitas' => fn ($q) => $q->orderBy('nama')])->orderBy('nama_tipe')->get(),
        );
    }

    public function show(TipeKamar $tipeKamar): TipeKamarResource
    {
        return new TipeKamarResource($tipeKamar->load(['fasilitas' => fn ($q) => $q->orderBy('nama')]));
    }

    public function store(TipeKamarRequest $request): JsonResponse
    {
        $data = $request->validated();

        $tipe = TipeKamar::query()->create($data);
        $tipe->fasilitas()->sync($data['fasilitas_ids'] ?? []);

        return (new TipeKamarResource($tipe->load('fasilitas')))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(TipeKamarRequest $request, TipeKamar $tipeKamar): TipeKamarResource
    {
        $data = $request->validated();

        $tipeKamar->update($data);

        if (array_key_exists('fasilitas_ids', $data)) {
            $tipeKamar->fasilitas()->sync($data['fasilitas_ids'] ?? []);
        }

        return new TipeKamarResource($tipeKamar->load('fasilitas'));
    }

    public function destroy(TipeKamar $tipeKamar): JsonResponse
    {
        if ($tipeKamar->kamar()->exists()) {
            return response()->json([
                'message' => 'Tipe kamar masih dipakai oleh kamar. Pindahkan atau hapus kamarnya dulu.',
            ], Response::HTTP_CONFLICT);
        }

        $tipeKamar->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
