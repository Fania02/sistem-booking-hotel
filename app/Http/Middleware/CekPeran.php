<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Pemakaian: ->middleware('peran:admin,resepsionis') */
class PeranDiizinkan
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        if ($user && in_array($user->role?->value, $peran, true)) {
            return $next($request);
        }

        // Tamu yang salah masuk ke area admin dikembalikan ke halamannya sendiri.
        if ($user && ! $request->expectsJson()) {
            return redirect()->route($user->adalahStaf() ? 'admin.dashboard' : 'tamu.dashboard');
        }

        abort(403, 'Anda tidak punya akses ke halaman ini.');
    }
}
