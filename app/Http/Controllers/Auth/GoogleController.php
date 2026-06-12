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

            // Verificar si requiere 2FA (y si no tiene cookie de dispositivo confiable)
            $trusted = false;
            $deviceCookie = request()->cookie('trusted_device_token');
            if ($deviceCookie) {
                $trustedDevice = \App\Models\TrustedDevice::where('user_id', $user->id)
                    ->where('device_hash', $deviceCookie)
                    ->first();
                if ($trustedDevice && $trustedDevice->last_used_at && $trustedDevice->last_used_at->diffInDays(now()) < 7) {
                    $trusted = true;
                }
            }

            if ($user->two_factor_secret && !$trusted) {
                // Requiere 2FA, guardar el ID en sesión y redirigir al challenge
                request()->session()->put([
                    'login.id' => $user->getKey(),
                    'login.remember' => true,
                ]);
                return redirect()->route('two-factor.login');
            }

            // Iniciar sesión directamente si no requiere 2FA o el dispositivo es confiable
            Auth::login($user, true);

            // Al iniciar sesión con Google, confirmamos automáticamente la sesión
            // para que Fortify no pida contraseña al momento de habilitar el 2FA.
            request()->session()->put('auth.password_confirmed_at', time());

            // Redirigir al dashboard (que pasará por profile completion middleware automáticamente)
            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            \Log::error('Error en Google Login: ' . $e->getMessage());
            return redirect('/login')->withErrors(['error' => 'Ocurrió un error al intentar autenticarse con Google. Por favor, intenta de nuevo.']);
        }
    }
}
