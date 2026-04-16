<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFaceVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Ignorar para rutas de escape seguras (logout, completado de perfil, la propia verificacion)
            if ($request->routeIs('face.verify.*') || 
                $request->routeIs('logout') || 
                $request->routeIs('profile.complete.*') ||
                $request->routeIs('device.verification.*')) {
                return $next($request);
            }

            $user = Auth::user();
            
            // Si el usuario ya tiene la foto guardada, requerirle que lo escanee
            if ($user->descriptor_facial && !session('face_verified', false)) {
                return redirect()->route('face.verify.form');
            }
        }

        return $next($request);
    }
}
