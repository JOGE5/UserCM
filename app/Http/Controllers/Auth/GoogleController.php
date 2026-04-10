<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use App\Mail\UserWelcomeMail;
use Illuminate\Support\Facades\Mail;

class GoogleController extends Controller
{
    /**
     * Redirige al usuario a la página de autenticación de Google.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtiene la información del usuario desde Google y lo registra o inicia sesión.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Buscar usuario existente por email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Si el usuario existe, actualizar su google_id si no lo tiene
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
                // Crear un nuevo usuario
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(24)),
                    'google_id' => $googleUser->getId(),
                ]);

                // Enviar el correo de bienvenida al nuevo usuario
                Mail::to($user->email)->send(new UserWelcomeMail($user));
            }

            // Iniciar sesión
            Auth::login($user);

            // Redirigir al dashboard (que pasará por device verification middleware automáticamente)
            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            \Log::error('Error en Google Login: ' . $e->getMessage());
            return redirect('/login')->withErrors(['error' => 'Ocurrió un error al intentar autenticarse con Google. Por favor, intenta de nuevo.']);
        }
    }
}
