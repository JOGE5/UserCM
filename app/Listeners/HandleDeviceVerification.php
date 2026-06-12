<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\TrustedDevice;
use App\Mail\DeviceVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class HandleDeviceVerification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(Login $event): void
    {
        $user = $event->user;
        $request = request();

        // Si el usuario marcó la casilla de confiar en el dispositivo durante el desafío TOTP
        if ($request->boolean('trust_device')) {
            $token = \Illuminate\Support\Str::random(60);

            TrustedDevice::create([
                'user_id' => $user->id,
                'device_hash' => $token, // Usamos la columna device_hash para guardar el token único
                'device_name' => substr($request->userAgent(), 0, 255),
                'ip_address' => $request->ip(),
                'last_used_at' => now(),
            ]);

            // Guardamos la cookie por 7 días
            \Illuminate\Support\Facades\Cookie::queue('trusted_device_token', $token, 60 * 24 * 7);
        }
    }
}
