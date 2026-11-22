<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Peran;
use App\Http\Controllers\Controller;
use App\Models\Tamu;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $kredensial = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($kredensial, $request->boolean('ingat'))) {
            throw ValidationException::withMessages(['email' => 'Email atau password salah.']);
        }

        $request->session()->regenerate();

        return response()->json(['data' => $this->ringkas($request->user())]);
    }

    public function registrasi(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'nik' => ['required', 'digits:16', 'unique:tamu,nik'],
            'no_hp' => ['required', 'string', 'max:15'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($data): User {
            $user = new User(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
            $user->role = Peran::Tamu;
            $user->save();

            Tamu::create([
                'user_id' => $user->id,
                'nama' => $data['name'],
                'nik' => $data['nik'],
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'] ?? '',
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['data' => $this->ringkas($user)], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['data' => null]);
    }

    /** @return array{id: int, name: string, email: string, peran: string, staf: bool} */
    private function ringkas(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'peran' => $user->role->value,
            'staf' => $user->adalahStaf(),
        ];
    }
}
