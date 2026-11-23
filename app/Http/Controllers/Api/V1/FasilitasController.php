<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FasilitasRequest;
use App\Http\Resources\FasilitasResource;
use App\Models\Fasilitas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FasilitasController extends Controller
{
    /**
     * Menampilkan semua fasilitas.
     */
    public function index(): AnonymousResourceCollection
    {
        return FasilitasResource::collection(
            Fasilitas::query()
                ->withCount('tipeKamar')
                ->orderBy('nama')
                ->get()
        );
    }

    /**
     * Menambahkan fasilitas.
     */
    public function store(FasilitasRequest $request): JsonResponse
    {
        $fasilitas = Fasilitas::query()->create($request->validated());

        return (new FasilitasResource($fasilitas))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Memperbarui fasilitas.
     */
    public function update(
        FasilitasRequest $request,
        Fasilitas $fasilitas
    ): FasilitasResource {
        $fasilitas->update($request->validated());

        return new FasilitasResource($fasilitas);
    }

    /**
     * Menghapus fasilitas sekaligus melepasnya dari semua tipe kamar.
     */
    public function destroy(Fasilitas $fasilitas): JsonResponse
    {
        $fasilitas->tipeKamar()->detach();
        $fasilitas->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}