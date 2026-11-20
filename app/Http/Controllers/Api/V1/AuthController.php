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
     * Mendaftarkan akun tamu. Akun dihubungkan ke data tamu berdasarkan NIK:
     * NIK baru membuat data tamu, NIK lama hanya boleh dipakai bila nama sama
     * dan belum terhubung ke akun lain.
     */
    public function register(RegistrasiRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data): User {
            $tamu = Tamu::withTrashed()->where('nik', $data['nik'])->first();

            if ($tamu !== null && $tamu->user_id !== null) {
                throw ValidationException::withMessages(['nik' => 'NIK ini sudah terhubung ke akun lain.']);
            }

            if ($tamu !== null && Str::lower(trim($tamu->nama)) !== Str::lower(trim($data['name']))) {
                throw ValidationException::withMessages(['nik' => 'NIK sudah terdaftar atas nama lain. Periksa kembali nama dan NIK Anda.']);
            }

            $user = new User(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
            $user->forceFill(['role' => Peran::Tamu])->save();

            $tamu ??= new Tamu(['nama' => $data['name'], 'nik' => $data['nik']]);
            $tamu->fill(['no_hp' => $data['no_hp'], 'alamat' => $data['alamat'] ?? $tamu->alamat, 'user_id' => $user->id]);
            $tamu->deleted_at = null;
            $tamu->save();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return (new UserResource($user))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): UserResource
    {
        $kunci = Str::lower($request->string('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            $detik = RateLimiter::availableIn($kunci);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$detik} detik.",
            ])->status(Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($kunci, 60);

            throw ValidationException::withMessages(['email' => 'Email atau password salah.']);
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
}
