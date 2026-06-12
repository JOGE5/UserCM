<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AdminLoginController extends Controller
{
    /**
     * Mostrar el formulario de login dedicado del panel de administración.
     */
    public function create()
    {
        return Inertia::render('Admin/Login');
    }

    /**
     * Procesar el login del panel de administración.
     *
     * Solo permite el acceso a usuarios con perfil de SuperAdministrador (1)
     * o Moderador (2). El resto recibe un error sin iniciar sesión.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        $perfil = $user->usuarioCampusMarket;

        if (! $perfil || ! in_array($perfil->Cod_Rol, [1, 2])) {
            throw ValidationException::withMessages([
                'email' => 'Esta cuenta no tiene acceso al panel de administración.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Tras la verificación de dispositivo (o directamente, si el dispositivo
        // ya es de confianza) el usuario debe aterrizar en el panel.
        $request->session()->put('url.intended', route('admin.dashboard'));

        if ($request->session()->get('device_verification_required')) {
            return redirect()->route('device.verification.form');
        }

        return redirect()->intended(route('admin.dashboard'));
    }
}
