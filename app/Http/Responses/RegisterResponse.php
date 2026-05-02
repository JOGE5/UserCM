<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        Auth::logout();
        $request->session()->regenerateToken();

        // Redirigir al login con un mensaje de éxito
        return redirect()->route('login')->with('status', 'Registro exitoso. Inicia sesión para continuar y completar tu perfil.');
    }
}
