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
        if (Auth::check()) {
            $perfil = Auth::user()->usuarioCampusMarket;
            // Si no tiene perfil, o si lo tiene pero le falta el id de universidad/carrera obligatorio
            if (!$perfil || empty($perfil->Cod_Carrera) || empty($perfil->Cod_Universidad)) {
                if (! $request->routeIs('profile.complete') && 
                    ! $request->routeIs('profile.complete.*') && 
                    ! $request->routeIs('device.verification.*') && 
                    ! $request->routeIs('logout')) {
                    return redirect()->route('profile.complete.form');
                }
            }
        }

        return $next($request);
    }
}
