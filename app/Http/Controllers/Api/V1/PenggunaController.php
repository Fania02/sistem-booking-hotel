<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Peran;
use App\Http\Controllers\Controller;
use App\Http\Requests\PenggunaRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Akun petugas (admin & resepsionis). Hanya admin. Akun tamu tidak dikelola di sini.
 */
class PenggunaController extends Controller
{
    private function staf()
    {
        return User::query()->whereIn('role', array_map(fn (Peran $p) => $p->value, Peran::staf()));
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $cari = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? null;

        return UserResource::collection(
            $this->staf()
                ->when($cari, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$cari}%")->orWhere('email', 'like', "%{$cari}%")))
                ->orderBy('name')
                ->get(),
        );
    }

    public function show(User $pengguna): UserResource
    {
        return new UserResource($this->pastikanStaf($pengguna));
    }

    public function store(PenggunaRequest $request): JsonResponse
    {
        $data = $request->validated();

        $pengguna = new User(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
        $pengguna->forceFill(['role' => $data['peran']])->save();

        return (new UserResource($pengguna))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(PenggunaRequest $request, User $pengguna): UserResource
    {
        $this->pastikanStaf($pengguna);
        $data = $request->validated();

        if ($pengguna->role === Peran::Admin && $data['peran'] !== Peran::Admin->value) {
            if ($pengguna->is($request->user())) {
                throw ValidationException::withMessages(['peran' => 'Anda tidak dapat menurunkan peran akun Anda sendiri.']);
            }
            $this->pastikanMasihAdaAdmin($pengguna);
        }

        $pengguna->fill(['name' => $data['name'], 'email' => $data['email']]);
        if (! empty($data['password'])) {
            $pengguna->password = $data['password'];
        }
        $pengguna->forceFill(['role' => $data['peran']])->save();

        return new UserResource($pengguna);
    }

    public function destroy(Request $request, User $pengguna): JsonResponse
    {
        $this->pastikanStaf($pengguna);

        if ($pengguna->is($request->user())) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun Anda sendiri.'], Response::HTTP_CONFLICT);
        }

        if ($pengguna->role === Peran::Admin) {
            $this->pastikanMasihAdaAdmin($pengguna);
        }

        $pengguna->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function pastikanStaf(User $pengguna): User
    {
        abort_unless($pengguna->adalahStaf(), Response::HTTP_NOT_FOUND);

        return $pengguna;
    }

    private function pastikanMasihAdaAdmin(User $kecuali): void
    {
        $lain = User::query()->where('role', Peran::Admin->value)->whereKeyNot($kecuali->getKey())->exists();

        if (! $lain) {
            throw ValidationException::withMessages(['peran' => 'Minimal harus ada satu akun admin.']);
        }
    }
}
