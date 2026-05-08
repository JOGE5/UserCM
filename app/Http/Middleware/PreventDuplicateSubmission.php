<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateSubmission
{
    public function handle(Request $request, Closure $next): Response
    {
        // Solo actúa en POST con usuario autenticado
        if (! $request->isMethod('POST') || ! auth()->check()) {
            return $next($request);
        }

        // Excluir archivos del hash (son binarios y nunca son duplicados por sí solos)
        $body = $request->except(array_keys($request->allFiles()));

        $hash = md5(json_encode($body) . '|' . auth()->id() . '|' . $request->url());
        $cacheKey = 'dup_submit_' . $hash;

        if (Cache::has($cacheKey)) {
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return response()->json(['error' => 'Solicitud duplicada detectada. Espera un momento antes de enviar de nuevo.'], 429);
            }
            return back()->withErrors(['error' => 'Solicitud duplicada detectada. Espera un momento.'])->withInput();
        }

        // Marcar como procesada por 60 segundos
        Cache::put($cacheKey, true, 60);

        return $next($request);
    }
}
