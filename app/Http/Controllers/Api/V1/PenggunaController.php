<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Peran;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Kelola akun (khusus admin). */
class PenggunaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filter = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'peran' => ['nullable', Rule::enum(Peran::class)],
        ]);

        $daftar = User::query()
            ->when($filter['search'] ?? null, fn ($q, $cari) => $q->where(fn ($w) => $w->where('name', 'like', "%{$cari}%")->orWhere('email', 'like', "%{$cari}%")))
            ->when($filter['peran'] ?? null, fn ($q, $peran) => $q->where('role', $peran))
            ->orderBy('name')
            ->limit(200)
            ->get();

        return response()->json(['data' => $daftar->map(fn (User $u) => $this->bentuk($u, $request))->values()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'peran' => ['required', Rule::enum(Peran::class)],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = new User(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
        $user->role = Peran::from($data['peran']);
        $user->save();

        return response()->json(['data' => $this->bentuk($user, $request)], 201);
    }

    public function update(Request $request, User $pengguna): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna->id)],
            'peran' => ['required', Rule::enum(Peran::class)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if ($pengguna->id === $request->user()->id && $data['peran'] !== $pengguna->role->value) {
            throw ValidationException::withMessages(['peran' => 'Kamu tidak bisa mengubah peran akunmu sendiri.']);
        }

        $pengguna->name = $data['name'];
        $pengguna->email = $data['email'];
        $pengguna->role = Peran::from($data['peran']);

        if (! empty($data['password'])) {
            $pengguna->password = $data['password'];
        }

        $pengguna->save();

        return response()->json(['data' => $this->bentuk($pengguna, $request)]);
    }

    public function destroy(Request $request, User $pengguna): Response|JsonResponse
    {
        if ($pengguna->id === $request->user()->id) {
            return response()->json(['message' => 'Kamu tidak bisa menghapus akunmu sendiri.'], 409);
        }

        $pengguna->delete();

        return response()->noContent();
    }

    /** @return array<string, mixed> */
    private function bentuk(User $u, Request $request): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'peran' => ['value' => $u->role->value, 'label' => $u->role->label()],
            'saya' => $u->id === $request->user()->id,
            'dibuat_pada' => $u->created_at?->toIso8601String(),
        ];
    }
}
