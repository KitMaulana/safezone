<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /** Paksa ganti kata sandi pada login pertama. */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && ! $request->routeIs('password.change', 'logout')) {
            return redirect()->route('password.change')
                ->with('info', 'Demi keamanan, silakan ganti kata sandi awal Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
