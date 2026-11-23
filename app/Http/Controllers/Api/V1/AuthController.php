<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Peran;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistrasiRequest;
use App\Http\Resources\UserResource;
use App\Models\Tamu;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Mendaftarkan akun tamu menggunakan data NIK.
     */
    public function register(RegistrasiRequest $request): JsonResponse
    {
        $user = $this->buatAkun($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        return (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Alternatif endpoint registrasi untuk frontend.
     */
    public function registrasi(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'nik' => ['required', 'digits:16'],
            'no_hp' => ['required', 'string', 'max:15'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->buatAkun($data);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'data' => $this->ringkas($user),
        ], Response::HTTP_CREATED);
    }

    /**
     * Membuat akun dan menghubungkannya dengan data tamu.
     */
    private function buatAkun(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $tamu = Tamu::withTrashed()
                ->where('nik', $data['nik'])
                ->first();

            if ($tamu !== null && $tamu->user_id !== null) {
                throw ValidationException::withMessages([
                    'nik' => 'NIK ini sudah terhubung ke akun lain.',
                ]);
            }

            if (
                $tamu !== null &&
                Str::lower(trim($tamu->nama)) !== Str::lower(trim($data['name']))
            ) {
                throw ValidationException::withMessages([
                    'nik' => 'NIK sudah terdaftar atas nama lain. Periksa kembali nama dan NIK Anda.',
                ]);
            }

            $user = new User([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $user->forceFill(['role' => Peran::Tamu])->save();

            $tamu ??= new Tamu([
                'nama' => $data['name'],
                'nik' => $data['nik'],
            ]);

            $tamu->fill([
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'] ?? $tamu->alamat,
                'user_id' => $user->id,
            ]);

            $tamu->deleted_at = null;
            $tamu->save();

            return $user;
        });
    }

    public function login(LoginRequest $request): UserResource
    {
        $kunci = Str::lower($request->string('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            $detik = RateLimiter::availableIn($kunci);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$detik} detik.",
            ])->status(Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($kunci, 60);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();

        return new UserResource($request->user());
    }

    public function logout(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Ringkasan akun untuk respons frontend.
     */
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