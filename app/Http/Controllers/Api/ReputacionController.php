<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ReputacionEntreUsuarios;
use App\Models\Publicaciones;
use App\Services\MarkovReputationService;
use Illuminate\Http\Request;

class ReputacionController extends Controller
{
    protected $markovService;

    public function __construct()
    {
        $this->markovService = new MarkovReputationService();
    }

    /**
     * POST /api/reputacion/{id}
     * Calificar a un usuario
     */
    public function store(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'Puntuacion' => 'required|integer|min:1|max:5',
                'Comentario' => 'nullable|string|max:500',
            ]);

            /** @var int|null $authId */
            $authId = auth()->id();
            if (!$authId) {
                return response()->json(['error' => 'No autenticado', 'debug' => auth()->check()], 401);
            }

            /** @var int $userId */
            $userId = (int)$id;

            // Validar que no se califique a sí mismo
            if ($authId === $userId) {
                return response()->json(['error' => 'No puedes calificarte a ti mismo'], 403);
            }

            ReputacionEntreUsuarios::create([
                'ID_Usuario_Calificador' => $authId,
                'ID_Usuario_Calificado' => $userId,
                'Puntuacion' => $validated['Puntuacion'],
                'Comentario' => $validated['Comentario'] ?? null,
            ]);

            // Actualizar estado del usuario calificado
            $usuario = User::find($userId);
            if ($usuario) {
                $reputacion = $this->markovService->actualizarEstado($usuario);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Calificación registrada',
                    'reputacion' => $reputacion,
                ], 201);
            }

            return response()->json(['error' => 'Usuario no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al guardar la calificación',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * GET /api/reputacion/{id}
     * Obtener reputación de un usuario
     */
    public function show($id)
    {
        /** @var int $userId */
        $userId = (int)$id;
        
        $usuario = User::find($userId);
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $reputacion = $this->markovService->obtenerEstado($usuario);
        $promedio = $this->markovService->calcularPromedioCalificaciones($usuario);
        $totalCalificaciones = ReputacionEntreUsuarios::where('ID_Usuario_Calificado', $userId)->count();

        return response()->json([
            'user_id' => $userId,
            'nombre' => $usuario->name,
            'reputacion' => $reputacion,
            'promedio_puntuacion' => $promedio,
            'total_calificaciones' => $totalCalificaciones,
        ]);
    }

    /**
     * GET /api/publicaciones
     * Obtener publicaciones ordenadas por calificación promedio del vendedor (de mayor a menor)
     */
    public function publicacionesOrdenadas()
    {
        try {
            $publicaciones = Publicaciones::with([
                'vendedor.user.reputacionEstado',
                'categoria'
            ])
                ->where('estado', 'activa')
                ->get()
                ->map(function ($pub) {
                    $reputacion = $pub->vendedor?->user?->reputacionEstado;
                    $estadoOrdinal = 0;
                    
                    // Calcular promedio de calificaciones del vendedor
                    $promedio = $this->markovService->calcularPromedioCalificaciones($pub->vendedor?->user);

                    if ($reputacion) {
                        $estadoOrdinal = $this->markovService->obtenerOrdinalEstado($reputacion->estado_actual);
                    }

                    return [
                        'id' => $pub->id,
                        'Titulo_Publicacion' => $pub->Titulo_Publicacion,
                        'Descripcion_Publicacion' => $pub->Descripcion_Publicacion,
                        'Precio_Publicacion' => $pub->Precio_Publicacion,
                        'Imagen_Publicacion' => $pub->Imagen_Publicacion,
                        'categoria' => $pub->categoria ? [
                            'id' => $pub->categoria->Cod_Categoria,
                            'Nombre_Categoria' => $pub->categoria->Nombre_Categoria,
                        ] : null,
                        'vendedor' => [
                            'user_id' => $pub->vendedor?->user_id,
                            'user' => [
                                'id' => $pub->vendedor?->user?->id,
                                'name' => $pub->vendedor?->user?->name,
                                'reputacionEstado' => $reputacion ? [
                                    'id' => $reputacion->id,
                                    'estado_actual' => $reputacion->estado_actual,
                                    'p_malo' => $reputacion->p_malo,
                                    'p_regular' => $reputacion->p_regular,
                                    'p_bueno' => $reputacion->p_bueno,
                                    'p_excelente' => $reputacion->p_excelente,
                                ] : null,
                            ],
                        ],
                        'estado_ordinal' => $estadoOrdinal,
                        'calificacion_promedio' => $promedio,
                    ];
                })
                ->sortByDesc('calificacion_promedio')
                ->values();

            return response()->json($publicaciones);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
