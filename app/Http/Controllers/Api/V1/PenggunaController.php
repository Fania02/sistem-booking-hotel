<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Peran;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Password;

class PenggunaController extends Controller
{
    /** Daftar pengguna dengan filter (admin) */
    public function index(Request $request): JsonResponse
    {
        $filter = $request->validate([
            'search'   => ['nullable', 'string', 'max:100'],
            'role'     => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $users = User::query()
            ->when(
                $filter['search'] ?? null,
                fn ($q, $s) => $q->where(
                    fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")
                )
            )
            ->when($filter['role'] ?? null, fn ($q, $r) => $q->where('role', $r))
            ->latest()
            ->paginate($filter['per_page'] ?? 20);

        return response()->json($users);
    }

    /** Tambah pengguna baru (admin) */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'role'     => ['required', 'string'],
            'password' => ['required', Password::min(8)],
        ]);

        $user = User::create($data);

        return response()->json(['data' => $user, 'message' => 'Pengguna berhasil dibuat.'], 201);
    }

    /** Ubah data pengguna (admin) */
    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'role'     => ['sometimes', 'string'],
            'password' => ['nullable', Password::min(8)],
        ]);

        // Jangan update password jika kosong
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json(['data' => $user, 'message' => 'Pengguna diperbarui.']);
    }

    /** Hapus pengguna (admin) */
    public function destroy(User $user): Response
    {
        $user->delete();

        return response()->noContent();
    }
}
