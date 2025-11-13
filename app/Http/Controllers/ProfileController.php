<?php

namespace App\Http\Controllers;

use App\Models\Roles;
use App\Models\Universidad;
use App\Models\UsuarioCampusMarket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function showCompleteForm(Request $request)
    {
        $user = $request->user();
        $universidades = Universidad::all();

        return Inertia::render('Auth/CompleteProfile', [
            'user' => $user,
            'universidades' => $universidades,
        ]);
    }

    public function complete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'Apellidos' => 'required|string|max:255',
            'Genero' => 'required|in:Masculino,Femenino,Otro',
            'Telefono' => 'required|string|max:20',
            'Foto_de_perfil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'Foto_de_portada' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'Cod_Universidad' => 'required|exists:universidades,Cod_Universidad',
            'Cod_Carrera' => 'required|exists:carreras,Cod_Carrera',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = $request->user();

        // Verificar que no exista ya un perfil para este usuario
        if ($user->usuarioCampusMarket) {
            return back()->withErrors(['general' => 'El perfil ya está completo.']);
        }

        // Guardar imágenes si se proporcionan
        $fotoPerfilPath = null;
        $fotoPortadaPath = null;

        if ($request->hasFile('Foto_de_perfil')) {
            $fotoPerfilPath = $request->file('Foto_de_perfil')->store('fotos_perfil', 'public');
        }

        if ($request->hasFile('Foto_de_portada')) {
            $fotoPortadaPath = $request->file('Foto_de_portada')->store('fotos_portada', 'public');
        }

        // Asignar rol por defecto (Estudiante)
        $rolPorDefecto = Roles::where('Nombre_Rol', 'Estudiante')->first();
        $codRol = $rolPorDefecto ? $rolPorDefecto->Cod_Rol : null;

        // Crear el perfil extendido
        UsuarioCampusMarket::create([
            'user_id' => $user->id,
            'Apellidos' => $request->Apellidos,
            'Genero' => $request->Genero,
            'Estado' => 'Activo',
            'Telefono' => $request->Telefono,
            'Foto_de_perfil' => $fotoPerfilPath,
            'Foto_de_portada' => $fotoPortadaPath,
            'Cod_Rol' => $codRol,
            'Cod_Carrera' => $request->Cod_Carrera,
        ]);

        return redirect()->route('dashboard')->with('success', 'Perfil completado exitosamente.');
    }
}
