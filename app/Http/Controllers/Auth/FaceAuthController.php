<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FaceAuthController extends Controller
{
    /**
     * Valida el descriptor enviado por el Frontend contra la Base de Datos.
     * Si coincide (Euclidean Distance <= 0.6), aprueba el reto e inicia sesión opcionalmente.
     */
    public function verifyAndLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'descriptor_actual' => 'required|array',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado. Verifica tu correo.'], 404);
        }

        if (!$user->descriptor_facial) {
            return response()->json(['error' => 'No tienes un registro facial guardado. Usa tu contraseña.'], 400);
        }

        $guardado = $user->descriptor_facial;
        $actual = $request->descriptor_actual;

        // Comprobar estructura básica para evitar exceptions no controladas
        if (!is_array($guardado) || !is_array($actual) || count($guardado) < 100 || count($actual) < 100) {
             return response()->json(['error' => 'Datos faciales corruptos o inválidos.'], 400);
        }

        // Euclidean Distance matemática en PHP Backend (Súper Seguro)
        $distance = 0;
        $length = min(count($guardado), count($actual));
        for ($i = 0; $i < $length; $i++) {
            $distance += pow((float)$actual[$i] - (float)$guardado[$i], 2);
        }
        $distance = sqrt($distance);

        // Si la distancia es > 0.60 el rostro no se parece lo suficiente al original
        if ($distance > 0.60) {
            return response()->json(['error' => 'Rostro denegado.'], 401);
        }

        // == ESTA PARTE SUCEDE SÓLO SI EL ROSTRO ES VALIDAD0 ==

        // 1. Si no estaba logueado el usuario, le iniciamos la sesión oficial desde cero (Passwordless Face ID)
        if (!Auth::check()) {
            Auth::login($user, true); // login persistente
        } else {
            // 2. Si ya estaba logueado pero con otra cuenta (muy raro pero posible) forzamos cierre 
            if (Auth::id() !== $user->id) {
                return response()->json(['error' => 'Conflicto de sesión, recarga la página.'], 403);
            }
        }

        // Otorgar sello de Autenticación de Segundo Factor para saltar middleware
        session(['face_verified' => true]);

        return response()->json([
            'message' => 'Rostro aceptado con éxito',
            'redirect' => route('dashboard')
        ]);
    }
}
