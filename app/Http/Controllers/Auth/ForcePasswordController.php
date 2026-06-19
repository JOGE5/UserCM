<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ForcePasswordController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/ForceChangePassword');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->force_password_change = false;
        $user->save();

        return redirect()->intended('/dashboard')->with('success', 'Contraseña actualizada correctamente. ¡Bienvenido!');
    }
}
