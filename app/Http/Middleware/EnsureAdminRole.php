<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $perfil = $request->user()?->usuarioCampusMarket;

        if (!$perfil || !in_array($perfil->Cod_Rol, [1, 2])) {
            abort(403, 'No tienes permiso para acceder al panel de administración.');
        }

        return $next($request);
    }
}
