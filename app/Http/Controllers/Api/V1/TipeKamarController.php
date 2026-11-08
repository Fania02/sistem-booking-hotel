<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TipeKamarResource;
use App\Models\TipeKamar;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TipeKamarController extends Controller
{
    /**
     * Daftar tipe kamar (untuk dropdown). CRUD lengkap tipe kamar dikerjakan anggota lain.
     */
    public function index(): AnonymousResourceCollection
    {
        return TipeKamarResource::collection(TipeKamar::query()->orderBy('nama_tipe')->get());
    }
}
