<?php

namespace App\Http\Middleware;

use App\Enums\Peran;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi route berdasarkan peran akun. Pemakaian: ->middleware('peran:admin,resepsionis').
 */
class PastikanPeran
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $diizinkan = array_map(fn (string $nilai): Peran => Peran::from($nilai), $peran);

        if (! $request->user()?->punyaPeran(...$diizinkan)) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
