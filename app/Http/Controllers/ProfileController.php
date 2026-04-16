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
    public function showCompleteProfileForm(Request $request)
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
        $data = $request->all();

        // Inertia FormData puede enviar los 'null' como string "null"
        if (isset($data['Foto_de_perfil']) && $data['Foto_de_perfil'] === 'null') {
            $data['Foto_de_perfil'] = null;
        }
        if (isset($data['Foto_de_portada']) && $data['Foto_de_portada'] === 'null') {
            $data['Foto_de_portada'] = null;
        }

        $validator = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
            'Apellidos' => 'nullable|string|max:255',
            'Genero' => 'nullable|in:Masculino,Femenino,Otro',
            'Telefono' => 'nullable|string|max:20',
            'Foto_de_perfil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'Foto_de_portada' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'Cod_Universidad' => 'required|exists:universidades,Cod_Universidad',
            'Cod_Carrera' => 'required|exists:carreras,Cod_Carrera',
            'fotoBase64' => 'required|string',
            'descriptorFacial' => 'required|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = $request->user();

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

        // Verificar que no exista ya un perfil para este usuario
        try {
            $user->update([
                'foto_base64' => $request->fotoBase64,
                'descriptor_facial' => $request->descriptorFacial,
            ]);

            if ($user->usuarioCampusMarket) {
                // Si ya existe, actualizar el perfil existente
                $updateData = [
                    'Apellidos' => $request->Apellidos ? trim($request->Apellidos) : null,
                    'Genero' => $request->Genero ? trim($request->Genero) : null,
                    'Estado' => 'Activo',
                    'Telefono' => $request->Telefono ? trim($request->Telefono) : null,
                    'Cod_Carrera' => $request->Cod_Carrera,
                    'Cod_Universidad' => $request->Cod_Universidad,
                ];

                if ($codRol) {
                    $updateData['Cod_Rol'] = $codRol;
                }

                if ($fotoPerfilPath) $updateData['Foto_de_perfil'] = $fotoPerfilPath;
                if ($fotoPortadaPath) $updateData['Foto_de_portada'] = $fotoPortadaPath;

                $user->usuarioCampusMarket->update($updateData);
                
                return redirect()->route('dashboard')->with('success', 'Perfil actualizado exitosamente.');
            }

            // Crear el perfil extendido
            $createData = [
                'user_id' => $user->id,
                'Apellidos' => $request->Apellidos ? trim($request->Apellidos) : null,
                'Genero' => $request->Genero ? trim($request->Genero) : null,
                'Estado' => 'Activo',
                'Telefono' => $request->Telefono ? trim($request->Telefono) : null,
                'Foto_de_perfil' => $fotoPerfilPath,
                'Foto_de_portada' => $fotoPortadaPath,
                'Cod_Carrera' => $request->Cod_Carrera,
                'Cod_Universidad' => $request->Cod_Universidad,
            ];

            if ($codRol) {
                $createData['Cod_Rol'] = $codRol;
            }

            UsuarioCampusMarket::create($createData);

            return redirect()->route('dashboard')->with('success', 'Perfil completado exitosamente.');
            
        } catch (\Exception $e) {
            // Loguear el error verdadero para poder debuggear
            \Log::error('Error completando perfil: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['form_error' => 'Error en base de datos: ' . $e->getMessage()]);
        }
    }
}
