<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario no está autenticado, dejar continuar
        if (!auth()->check()) {
            return $next($request);
        }

        // Si ya está en la página de completar perfil, dejar pasar
        if ($request->path() === 'complete-profile') {
            return $next($request);
        }

        // Verificar si el usuario tiene perfil completado (UsuarioCampusMarket)
        $user = auth()->user();
        if (!$user->usuarioCampusMarket) {
            // Redirigir a completar perfil
            return redirect()->route('profile.complete.form');
        }

        return $next($request);
    }
}
