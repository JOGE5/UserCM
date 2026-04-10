<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Mail\PasswordResetCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PasswordResetCodeController extends Controller
{
    /**
     * View to request the code.
     */
    public function showRequestForm()
    {
        return Inertia::render('Auth/ForgotPasswordCode');
    }

    /**
     * Generate and send the alphanumeric code.
     */
    public function sendCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // Generate 8 char alphanumeric code
        $code = strtoupper(Str::random(8));

        // Save in DB
        PasswordResetCode::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
            'used' => false,
        ]);

        // Send email
        Mail::to($request->email)->send(new PasswordResetCodeMail($code));

        return redirect()->route('password.verify.code.form', ['email' => $request->email])
            ->with('status', 'Te hemos enviado el código de recuperación.');
    }

    /**
     * View to verify the code.
     */
    public function showVerifyForm(Request $request)
    {
        return Inertia::render('Auth/VerifyResetCode', [
            'email' => $request->query('email'),
            'status' => session('status'),
        ]);
    }

    /**
     * Verify that the code is valid.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:8',
        ]);

        $resetCode = PasswordResetCode::where('email', $request->email)
            ->where('code', strtoupper($request->code))
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetCode) {
            return back()->withErrors(['code' => 'El código es inválido o ha expirado.']);
        }

        // Store email temporarily to allow resetting directly
        session(['verified_reset_email' => $request->email, 'verified_reset_code' => $resetCode->code]);

        return redirect()->route('password.reset.custom.form');
    }

    /**
     * View to reset the password.
     */
    public function showResetForm()
    {
        if (!session('verified_reset_email')) {
            return redirect()->route('password.request.custom');
        }

        return Inertia::render('Auth/ResetPasswordCode', [
            'email' => session('verified_reset_email'),
        ]);
    }

    /**
     * Update the password and consume the code.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $verifiedEmail = session('verified_reset_email');
        $codeStr = session('verified_reset_code');

        if ($request->email !== $verifiedEmail) {
            return back()->withErrors(['email' => 'Sesión expirada o inválida.']);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        // Mark code as used
        PasswordResetCode::where('email', $request->email)
            ->where('code', $codeStr)
            ->update(['used' => true]);

        session()->forget(['verified_reset_email', 'verified_reset_code']);

        return redirect()->route('login')->with('status', 'Tu contraseña ha sido restablecida con éxito.');
    }
}
