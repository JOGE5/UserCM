<?php

namespace App\Http\Controllers;

use App\Models\Publicaciones;
use App\Models\UsuarioCampusMarket;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
            'Imagen_Publicacion' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Publicaciones $publicaciones)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Publicaciones $publicaciones)
    {
        //
    }
}
