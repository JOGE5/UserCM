<?php

namespace App\Http\Controllers;

use App\Models\Publicaciones;
use App\Models\UsuarioCampusMarket;
use App\Models\Categorias;
use App\Models\User;
use App\Models\ReputacionEntreUsuarios;
use App\Rules\NoProfanity;
use App\Services\MarkovReputationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PublicacionesController extends Controller
{
    /**
     * Sirve el dashboard principal con filtros y paginación.
     *
     * Usa when() en lugar de if/else para construir la query: when() mantiene
     * el encadenamiento fluent de Eloquent y aplica el bloque solo si la
     * condición es verdadera, sin romper la cadena ni necesitar variables
     * intermedias.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $usuarioCampusMarket = UsuarioCampusMarket::where('user_id', $userId)->first();
        $userEstado = $usuarioCampusMarket?->Estado;

        $orden = $request->input('orden', 'fecha_desc');
        $orderColumn = match($orden) {
            'precio_asc', 'precio_desc' => 'Precio_Publicacion',
            default                     => 'created_at',
        };
        $orderDirection = match($orden) {
            'fecha_asc', 'precio_asc' => 'asc',
            default                   => 'desc',
        };

        $publicaciones = Publicaciones::with('categoria', 'vendedor.user')
            ->where('estado', 'activa')
            ->when($request->filled('precio_min'), fn ($q) =>
                $q->where('Precio_Publicacion', '>=', $request->precio_min))
            ->when($request->filled('precio_max'), fn ($q) =>
                $q->where('Precio_Publicacion', '<=', $request->precio_max))
            ->when($request->filled('ubicacion'), fn ($q) =>
                $q->where('ubicacion', 'like', '%'.$request->ubicacion.'%'))
            ->when($request->filled('condicion'), fn ($q) =>
                $q->where('condicion_producto', $request->condicion))
            ->orderBy($orderColumn, $orderDirection)
            ->paginate(15)
            ->withQueryString(); // preserva los filtros en los links de paginación

        try {
            $markov = new MarkovReputationService();
            $mejores = Publicaciones::with(['categoria', 'vendedor.user.reputacionEstado'])
                ->where('estado', 'activa')
                ->get()
                ->map(function ($pub) use ($markov) {
                    $promedio = $markov->calcularPromedioCalificaciones($pub->vendedor?->user);
                    $arr = $pub->toArray();
                    if (isset($arr['vendedor']) && $pub->vendedor?->user_id) {
                        $arr['vendedor']['user_id'] = $pub->vendedor->user_id;
                    }
                    return array_merge($arr, ['calificacion_promedio' => $promedio]);
                })
                ->sortByDesc('calificacion_promedio')
                ->values()
                ->take(6)
                ->all();
        } catch (\Throwable $e) {
            Log::error('Error calculando mejores valorados: '.$e->getMessage());
            $mejores = [];
        }

        return Inertia::render('Dashboard', [
            'publicaciones' => $publicaciones,
            'mejores'       => $mejores,
            'currentUserId' => $userId,
            'userEstado'    => $userEstado,
            'filters'       => $request->only(['precio_min', 'precio_max', 'ubicacion', 'condicion', 'orden']),
        ]);
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
        // Debug: registrar entrada a la acción para diagnosticar peticiones que se quedan "cargando"
        Log::info('PublicacionesController@store called', [
            'user_id' => Auth::id(),
            'input_keys' => array_keys($request->all()),
            'has_files' => $request->hasFile('Imagen_Publicacion') || count($request->allFiles()) > 0,
        ]);

        $validated = $request->validate([
            'Titulo_Publicacion'      => ['required', 'string', 'max:200', new NoProfanity()],
            'Descripcion_Publicacion' => ['required', 'string', new NoProfanity()],
            'Precio_Publicacion'      => 'required|numeric|min:1|max:1000000',
            'Cod_Categoria'           => 'required|integer',
            'Estado_Publicacion'      => 'boolean',
            'ubicacion'               => 'nullable|string|max:100',
            'condicion_producto'      => 'nullable|in:nuevo,usado',
            'Imagen_Publicacion'      => 'nullable|array|max:3',
            'Imagen_Publicacion.*'    => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Obtener o crear registro en usuarios_campus_markets para el usuario autenticado
        $userId = Auth::id();
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

        // Asignar el ID del vendedor (referencia a usuarios_campus_markets.id)
        $validated['ID_Vendedor'] = $vendor->id;

        // Por defecto, las nuevas publicaciones son 'activas'
        $validated['estado'] = 'activa';

        // Procesar y guardar imágenes (hasta 3) usando el método helper
        $paths = $this->handleImageUploads($request);
        $validated['Imagen_Publicacion'] = json_encode($paths);

        Publicaciones::create($validated);

        return redirect()->route('dashboard')->with('success', 'Publicación creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Publicaciones $publicaciones)
    {
        // Incrementar contador de vistas
        $publicaciones->increment('Vistas_Publicacion');

        // Publicaciones ocultas solo visibles para su dueño
        if ($publicaciones->estado === 'oculta') {
            $esVendedor = $publicaciones->vendedor?->user_id === Auth::id();
            if (! $esVendedor) {
                abort(404);
            }
        }

        $publicaciones->load(['categoria', 'vendedor.user.reputacionEstado']);

        $vendedorUserId = $publicaciones->vendedor?->user_id;
        $vendedorPromedioReal = $vendedorUserId
            ? ReputacionEntreUsuarios::where('ID_Usuario_Calificado', $vendedorUserId)->avg('Puntuacion')
            : null;

        return Inertia::render('Publicaciones/Show', [
            'publicacion'           => $publicaciones,
            'currentUserId'         => Auth::id(),
            'vendedor_promedio_real' => $vendedorPromedioReal !== null
                ? round((float) $vendedorPromedioReal, 1)
                : null,
        ]);
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
            'Titulo_Publicacion'      => ['sometimes', 'required', 'string', 'max:200', new NoProfanity()],
            'Descripcion_Publicacion' => ['sometimes', 'required', 'string', new NoProfanity()],
            'Precio_Publicacion'      => 'sometimes|required|numeric|min:1|max:1000000',
            'Cod_Categoria'           => 'sometimes|required|integer',
            'Estado_Publicacion'      => 'sometimes|boolean',
            'ubicacion'               => 'nullable|string|max:100',
            'condicion_producto'      => 'nullable|in:nuevo,usado',
            'Imagen_Publicacion'      => $imageRule === 'required|image|mimes:jpeg,png,jpg,gif|max:2048' ? 'required|array|min:1|max:3' : 'nullable|array|max:3',
            'Imagen_Publicacion.*'    => 'image|mimes:jpeg,png,jpg,gif|max:2048',
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
            // Procesar y guardar nuevas imágenes
            $paths = $this->handleImageUploads($request);
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
        if ($publicaciones->vendedor->user_id !== Auth::id()) {
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
        if ($publicaciones->vendedor->user_id !== Auth::id()) {
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
        if ($publicaciones->vendedor->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para activar esta publicación');
        }

        $publicaciones->update(['estado' => 'activa']);

        return redirect()->route('dashboard')->with('success', 'Publicación activada');
    }

    /**
     * Marcar una publicación como vendida.
     * Solo el dueño puede hacerlo. El campo comprador_id es opcional.
     */
    public function marcarVendido(Request $request, Publicaciones $publicaciones)
    {
        if ($publicaciones->vendedor->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'comprador_id' => 'nullable|exists:usuarios_campus_markets,id',
        ]);

        $publicaciones->update([
            'estado'       => 'vendida',
            'vendido_at'   => now(),
            'comprador_id' => $validated['comprador_id'] ?? null,
        ]);

        return redirect()->route('publicaciones.show', $publicaciones->id)
            ->with('success', '¡Producto marcado como vendido!');
    }

    /**
     * Calificar a un usuario con reputación
     */
    public function rateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'Puntuacion' => 'required|integer|min:1|max:5',
            'Comentario' => 'nullable|string|max:500',
        ]);

        /** @var int|null $authId */
        $authId = Auth::id();
        $validated['ID_Usuario_Calificador'] = $authId;
        $validated['ID_Usuario_Calificado'] = $user->id;

        ReputacionEntreUsuarios::create($validated);

        // Actualizar estado de reputación del usuario calificado
        $markovService = new MarkovReputationService();
        $markovService->actualizarEstado($user);

        return response()->json([
            'success' => true,
            'message' => 'Calificación registrada exitosamente',
        ]);
    }

    /**
     * Procesar y almacenar de 1 a 3 imágenes de la publicación.
     */
    private function handleImageUploads(Request $request): array
    {
        $files = $request->file('Imagen_Publicacion');
        
        if (empty($files)) {
            $collected = [];
            foreach ($request->allFiles() as $key => $val) {
                if (strpos($key, 'Imagen_Publicacion') === 0) {
                    if (is_array($val)) {
                        $collected = array_merge($collected, $val);
                    } else {
                        $collected[] = $val;
                    }
                }
            }
            $files = $collected;
        }

        $paths = [];
        $count = 0;
        if (!empty($files)) {
            foreach ($files as $file) {
                if (! $file) continue;
                if ($count >= 3) break;
                try {
                    $paths[] = $file->store('publicaciones', 'public');
                    $count++;
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }

        return $paths;
    }
}
