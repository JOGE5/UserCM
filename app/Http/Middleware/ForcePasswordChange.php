<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->force_password_change) {
            // Permitir que el usuario acceda a la ruta de cambio de contraseña y logout
            if (! $request->is('force-change-password') && ! $request->is('logout') && ! $request->routeIs('force.password.update')) {
                return redirect()->route('force.password.change');
            }
        }

        return $next($request);
    }
}
