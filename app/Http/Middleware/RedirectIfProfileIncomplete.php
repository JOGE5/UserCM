<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfProfileIncomplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->usuarioCampusMarket) {
            // Si el usuario está autenticado pero no tiene perfil completo, redirigir al formulario
            if (! $request->is('complete-profile*') && ! $request->is('logout')) {
                return redirect()->route('profile.complete.form');
            }
        }

        return $next($request);
    }
}
