<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TrustedDevice;
use App\Mail\DeviceVerificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class DeviceVerificationController extends Controller
{
    /**
     * Show device verification screen.
     */
    public function showVerificationForm()
    {
        if (!session('device_verification_required')) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/DeviceVerification');
    }

    /**
     * Verify the numeric code.
     */
    public function verify(Request $request)
    {
        if (!session('device_verification_required')) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);

        $sessionCode = session('device_verification_code');
        $expiresAt = session('device_verification_expires_at');

        if (!$sessionCode || now()->greaterThan($expiresAt)) {
            return back()->withErrors(['code' => 'El código ha expirado. Por favor solicita uno nuevo.']);
        }

        if ((int)$request->code !== (int)$sessionCode) {
            return back()->withErrors(['code' => 'El código ingresado es incorrecto.']);
        }

        // Registrar el dispositivo
        $userAgent = request()->userAgent();
        $ip = request()->ip();
        $deviceHash = hash('sha256', $userAgent . '|' . $ip);

        TrustedDevice::create([
            'user_id' => Auth::id(),
            'device_hash' => $deviceHash,
            'device_name' => substr($userAgent, 0, 255),
            'ip_address' => $ip,
            'last_used_at' => now(),
        ]);

        // Validate and clean up session
        session()->forget(['device_verification_required', 'device_verification_code', 'device_verification_expires_at']);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Resend verification code.
     */
    public function resend()
    {
        if (!session('device_verification_required')) {
            return redirect()->route('dashboard');
        }

        $code = rand(100000, 999999);

        session([
            'device_verification_code' => $code,
            'device_verification_expires_at' => now()->addMinutes(10),
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        Mail::to($user->email)->send(new DeviceVerificationMail($code, substr(request()->userAgent(), 0, 100)));

        return back()->with('status', 'Un nuevo código ha sido enviado a tu correo electrónico.');
    }
}
