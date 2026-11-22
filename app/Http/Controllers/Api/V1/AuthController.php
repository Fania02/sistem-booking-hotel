<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Peran;
use App\Http\Controllers\Controller;
use App\Models\Tamu;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Login: kembalikan data user + flag apakah staf */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($data, true)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $user = Auth::user();
        $request->session()->regenerate();

        return response()->json([
            'data' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role?->value,
                'staf'  => $user->adalahStaf(),
            ],
        ]);
    }

    /** Registrasi tamu baru — otomatis buat baris di tabel tamu */
    public function registrasi(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'nik'                   => ['nullable', 'digits:16'],
            'no_hp'                 => ['nullable', 'string', 'max:20'],
            'alamat'                => ['nullable', 'string'],
            'password'              => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'role'     => Peran::Tamu,
        ]);

        // Buat data tamu yang terhubung dengan akun ini
        Tamu::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nama'   => $data['name'],
                'nik'    => $data['nik'] ?? null,
                'no_hp'  => $data['no_hp'] ?? null,
                'alamat' => $data['alamat'] ?? null,
            ]
        );

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'data' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role?->value,
                'staf'  => false,
            ],
        ], 201);
    }

    /** Logout: hapus sesi */
    public function logout(Request $request): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
