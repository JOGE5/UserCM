<?php

namespace App\Http\Controllers;

use App\Models\Publicaciones;
use App\Models\UsuarioCampusMarket;
use App\Models\Categorias;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PublicacionesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Dashboard/CreatePublicacion');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Titulo_Publicacion' => 'required|string|max:200',
            'Descripcion_Publicacion' => 'required|string',
            'Precio_Publicacion' => 'required|numeric|min:0',
            'Cod_Categoria' => 'required|integer',
            'Estado_Publicacion' => 'boolean',
            // La imagen es obligatoria al crear una publicación
            'Imagen_Publicacion' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Obtener o crear registro en usuarios_campus_markets para el usuario autenticado
        $userId = auth()->id();
        $vendor = UsuarioCampusMarket::where('user_id', $userId)->first();

        if (! $vendor) {
            // Crear registro mínimo para vincular la publicación
            $vendor = UsuarioCampusMarket::create([
                'user_id' => $userId,
                'Apellidos' => '',
                'Genero' => null,
                'Estado' => 'Habilitado',
                'Telefono' => null,
                'Foto_de_portada' => null,
                'Foto_de_perfil' => null,
                'Cod_Rol' => 3,
                'Cod_Carrera' => null,
                'Cod_Universidad' => null,
            ]);
        }

        // Asignar el ID del vendedor (referencia a usuarios_campus_markets.ID_Usuario)
        $validated['ID_Vendedor'] = $vendor->ID_Usuario;

        // Por defecto, las nuevas publicaciones son 'activas'
        $validated['estado'] = 'activa';

        // Guardar imagen si existe
        if ($request->hasFile('Imagen_Publicacion')) {
            $path = $request->file('Imagen_Publicacion')->store('publicaciones', 'public');
            $validated['Imagen_Publicacion'] = $path;
        }

        Publicaciones::create($validated);

        return redirect()->route('dashboard')->with('success', 'Publicación creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Publicaciones $publicaciones)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Publicaciones $publicaciones)
    {
        $categorias = Categorias::all();

        return Inertia::render('EditPublicacion', [
            'publicacion' => $publicaciones,
            'categorias' => $categorias,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Publicaciones $publicaciones)
    {
        // Si la publicación no tiene imagen actualmente, exigir que se suba una nueva
        $imageRule = $publicaciones->Imagen_Publicacion ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' : 'required|image|mimes:jpeg,png,jpg,gif|max:2048';

        $validated = $request->validate([
            'Titulo_Publicacion' => 'sometimes|required|string|max:200',
            'Descripcion_Publicacion' => 'sometimes|required|string',
            'Precio_Publicacion' => 'sometimes|required|numeric|min:0',
            'Cod_Categoria' => 'sometimes|required|integer',
            'Estado_Publicacion' => 'sometimes|boolean',
            'Imagen_Publicacion' => $imageRule,
        ]);

        // Si vienen una nueva imagen, guardarla y eliminar la anterior (si existe)
        if ($request->hasFile('Imagen_Publicacion')) {
            $path = $request->file('Imagen_Publicacion')->store('publicaciones', 'public');

            // Eliminar la imagen antigua si existía
            if ($publicaciones->Imagen_Publicacion && Storage::disk('public')->exists($publicaciones->Imagen_Publicacion)) {
                Storage::disk('public')->delete($publicaciones->Imagen_Publicacion);
            }

            $validated['Imagen_Publicacion'] = $path;
        }

        $publicaciones->update($validated);

        return redirect()->route('dashboard')->with('success', 'Publicación actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Publicaciones $publicaciones)
    {
        // Verificar que sea el propietario
        if ($publicaciones->vendedor->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para eliminar esta publicación');
        }

        // Eliminación lógica (soft delete)
        $publicaciones->delete();

        return redirect()->route('dashboard')->with('success', 'Publicación eliminada correctamente');
    }

    /**
     * Cambiar publicación a borrador.
     */
    public function toDraft(Publicaciones $publicaciones)
    {
        // Verificar que sea el propietario
        if ($publicaciones->vendedor->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para cambiar esta publicación a borrador');
        }

        $publicaciones->update(['estado' => 'borrador']);

        return redirect()->route('dashboard')->with('success', 'Publicación convertida a borrador');
    }

    /**
     * Cambiar publicación a activa.
     */
    public function toActive(Publicaciones $publicaciones)
    {
        // Verificar que sea el propietario
        if ($publicaciones->vendedor->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para activar esta publicación');
        }

        $publicaciones->update(['estado' => 'activa']);

        return redirect()->route('dashboard')->with('success', 'Publicación activada');
    }
}
