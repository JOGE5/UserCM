<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        // Cerrar sesión al usuario que Fortify acaba de loguear automáticamente
        auth()->logout();
        
        // Destruir por completo la sesión para forzar un nuevo token CSRF limpio
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirigir al login con un mensaje de éxito
        return redirect()->route('login')->with('status', 'Registro exitoso. Inicia sesión para continuar y completar tu perfil.');
    }
}
