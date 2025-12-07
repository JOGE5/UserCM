<?php

namespace App\Http\Controllers;

use App\Models\Publicaciones;
use App\Models\UsuarioCampusMarket;
use App\Models\Categorias;
use App\Rules\NoProfanity;
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
            'Titulo_Publicacion' => ['required','string','max:200', new NoProfanity()],
            'Descripcion_Publicacion' => ['required','string', new NoProfanity()],
            'Precio_Publicacion' => 'required|numeric|min:0',
            'Cod_Categoria' => 'required|integer',
            'Estado_Publicacion' => 'boolean',
            // Soportar hasta 3 imágenes: se espera array de archivos
            'Imagen_Publicacion' => 'required|array|min:1|max:3',
            'Imagen_Publicacion.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Obtener o crear registro en usuarios_campus_markets para el usuario autenticado
        $userId = auth()->id();
        $vendor = UsuarioCampusMarket::where('user_id', $userId)->first();

        if (! $vendor) {
            // Crear registro mínimo para vincular la publicación sin incluir campos nulos
            $vendorData = [
                'user_id' => $userId,
                'Estado' => 'Habilitado',
                'Cod_Rol' => 3,
            ];

            // Solo incluir campos si tienen valor no nulo
            // (evita insertar explicitamente NULL que pueda causar errores si la columna es NOT NULL)
            // Apellidos/Telefono/Foto_* pueden omitirse aquí y completarse desde el perfil

            $vendor = UsuarioCampusMarket::create($vendorData);
        }

        // Asignar el ID del vendedor (referencia a usuarios_campus_markets.ID_Usuario)
        $validated['ID_Vendedor'] = $vendor->ID_Usuario;

        // Por defecto, las nuevas publicaciones son 'activas'
        $validated['estado'] = 'activa';

        // Guardar imágenes (hasta 3)
        $paths = [];
        // Intentar obtener la colección normal de archivos
        $files = $request->file('Imagen_Publicacion');

        // Si no hay 'Imagen_Publicacion' como array, buscar en allFiles() (p. ej. keys Imagen_Publicacion.0)
        if (empty($files)) {
            $all = $request->allFiles();
            $collected = [];
            foreach ($all as $key => $val) {
                if (strpos($key, 'Imagen_Publicacion') === 0) {
                    if (is_array($val)) {
                        $collected = array_merge($collected, $val);
                    } else {
                        $collected[] = $val;
                    }
                }
            }
            if (!empty($collected)) $files = $collected;
        }

        if (!empty($files)) {
            $count = 0;
            foreach ($files as $file) {
                if (! $file) continue;
                if ($count >= 3) break;
                try {
                    $paths[] = $file->store('publicaciones', 'public');
                } catch (\Throwable $e) {
                    // si ocurre un error con un archivo específico, ignorarlo pero seguir
                    continue;
                }
                $count++;
            }
        }

        $validated['Imagen_Publicacion'] = json_encode($paths);

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
            'Titulo_Publicacion' => ['sometimes','required','string','max:200', new NoProfanity()],
            'Descripcion_Publicacion' => ['sometimes','required','string', new NoProfanity()],
            'Precio_Publicacion' => 'sometimes|required|numeric|min:0',
            'Cod_Categoria' => 'sometimes|required|integer',
            'Estado_Publicacion' => 'sometimes|boolean',
            // Imagenes opcionales: array de archivos
            'Imagen_Publicacion' => $imageRule === 'required|image|mimes:jpeg,png,jpg,gif|max:2048' ? 'required|array|min:1|max:3' : 'nullable|array|max:3',
            'Imagen_Publicacion.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Si vienen una nueva imagen, guardarla y eliminar la anterior (si existe)
        if ($request->hasFile('Imagen_Publicacion') || count($request->allFiles()) > 0) {
            // eliminar imágenes antiguas si existían
            try {
                $old = $publicaciones->Imagen_Publicacion;
                $oldPaths = is_string($old) ? @json_decode($old, true) : $old;
                if (is_array($oldPaths)) {
                    foreach ($oldPaths as $p) {
                        if ($p && Storage::disk('public')->exists($p)) {
                            Storage::disk('public')->delete($p);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
            // Recolectar archivos incluso si vienen como Imagen_Publicacion.0, Imagen_Publicacion.1, etc.
            $files = $request->file('Imagen_Publicacion');
            if (empty($files)) {
                $all = $request->allFiles();
                $collected = [];
                foreach ($all as $key => $val) {
                    if (strpos($key, 'Imagen_Publicacion') === 0) {
                        if (is_array($val)) {
                            $collected = array_merge($collected, $val);
                        } else {
                            $collected[] = $val;
                        }
                    }
                }
                if (!empty($collected)) $files = $collected;
            }

            $paths = [];
            $count = 0;
            if (!empty($files)) {
                foreach ($files as $file) {
                    if (! $file) continue;
                    if ($count >= 3) break;
                    try {
                        $paths[] = $file->store('publicaciones', 'public');
                    } catch (\Throwable $e) {
                        continue;
                    }
                    $count++;
                }
            }

            $validated['Imagen_Publicacion'] = json_encode($paths);
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
