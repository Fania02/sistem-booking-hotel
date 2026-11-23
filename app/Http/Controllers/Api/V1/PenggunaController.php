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

/**
 * Pengelolaan akun petugas (admin dan resepsionis).
 * Akun tamu tidak dikelola melalui controller ini.
 */
class PenggunaController extends Controller
{
    private function staf()
    {
        return User::query()->whereIn(
            'role',
            array_map(fn (Peran $p) => $p->value, Peran::staf())
        );
    }

    public function index(Request $request): JsonResponse
    {
        $filter = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'peran' => ['nullable', Rule::enum(Peran::class)],
        ]);

        $daftar = $this->staf()
            ->when(
                $filter['search'] ?? null,
                fn ($q, $cari) => $q->where(
                    fn ($w) => $w
                        ->where('name', 'like', "%{$cari}%")
                        ->orWhere('email', 'like', "%{$cari}%")
                )
            )
            ->when(
                $filter['peran'] ?? null,
                fn ($q, $peran) => $q->where('role', $peran)
            )
            ->orderBy('name')
            ->limit(200)
            ->get();

        return response()->json([
            'data' => $daftar
                ->map(fn (User $u) => $this->bentuk($u, $request))
                ->values(),
        ]);
    }

    public function show(User $pengguna, Request $request): JsonResponse
    {
        $this->pastikanStaf($pengguna);

        return response()->json([
            'data' => $this->bentuk($pengguna, $request),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'peran' => ['required', Rule::enum(Peran::class)],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $peran = Peran::from($data['peran']);

        if (! in_array($peran, Peran::staf(), true)) {
            throw ValidationException::withMessages([
                'peran' => 'Akun yang dikelola hanya admin dan resepsionis.',
            ]);
        }

        $pengguna = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $pengguna->forceFill(['role' => $peran])->save();

        return response()->json([
            'data' => $this->bentuk($pengguna, $request),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, User $pengguna): JsonResponse
    {
        $this->pastikanStaf($pengguna);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($pengguna->id),
            ],
            'peran' => ['required', Rule::enum(Peran::class)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $peranBaru = Peran::from($data['peran']);

        if (! in_array($peranBaru, Peran::staf(), true)) {
            throw ValidationException::withMessages([
                'peran' => 'Akun petugas hanya dapat memiliki peran admin atau resepsionis.',
            ]);
        }

        if ($pengguna->is($request->user()) && $peranBaru !== $pengguna->role) {
            throw ValidationException::withMessages([
                'peran' => 'Anda tidak dapat mengubah peran akun Anda sendiri.',
            ]);
        }

        if (
            $pengguna->role === Peran::Admin &&
            $peranBaru !== Peran::Admin
        ) {
            $this->pastikanMasihAdaAdmin($pengguna);
        }

        $pengguna->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $pengguna->password = $data['password'];
        }

        $pengguna->forceFill(['role' => $peranBaru])->save();

        return response()->json([
            'data' => $this->bentuk($pengguna, $request),
        ]);
    }

    public function destroy(Request $request, User $pengguna): Response|JsonResponse
    {
        $this->pastikanStaf($pengguna);

        if ($pengguna->is($request->user())) {
            return response()->json([
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ], Response::HTTP_CONFLICT);
        }

        if ($pengguna->role === Peran::Admin) {
            $this->pastikanMasihAdaAdmin($pengguna);
        }

        $pengguna->delete();

        return response()->noContent();
    }

    private function pastikanStaf(User $pengguna): User
    {
        abort_unless(
            $pengguna->adalahStaf(),
            Response::HTTP_NOT_FOUND
        );

        return $pengguna;
    }

    private function pastikanMasihAdaAdmin(User $kecuali): void
    {
        $lain = User::query()
            ->where('role', Peran::Admin->value)
            ->whereKeyNot($kecuali->getKey())
            ->exists();

        if (! $lain) {
            throw ValidationException::withMessages([
                'peran' => 'Minimal harus ada satu akun admin.',
            ]);
        }
    }

    /**
     * Membentuk data akun untuk frontend.
     *
     * @return array<string, mixed>
     */
    private function bentuk(User $u, Request $request): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'peran' => [
                'value' => $u->role->value,
                'label' => $u->role->label(),
            ],
            'saya' => $u->id === $request->user()->id,
            'dibuat_pada' => $u->created_at?->toIso8601String(),
        ];
    }
}