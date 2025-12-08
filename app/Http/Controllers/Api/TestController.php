<?php

namespace App\Http\Controllers\Api;

use App\Models\Publicaciones;
use Illuminate\Routing\Controller;

class TestController extends Controller
{
    public function test()
    {
        $publicaciones = Publicaciones::with([
            'vendedor.user.reputacionEstado',
            'categoria'
        ])
            ->where('estado', 'activa')
            ->get()
            ->map(function ($pub) {
                return [
                    'id' => $pub->id,
                    'Titulo_Publicacion' => $pub->Titulo_Publicacion,
                    'Descripcion_Publicacion' => $pub->Descripcion_Publicacion,
                    'Precio_Publicacion' => $pub->Precio_Publicacion,
                    'Imagen_Publicacion' => $pub->Imagen_Publicacion,
                    'categoria' => $pub->categoria ? [
                        'id' => $pub->categoria->id,
                        'Nombre_Categoria' => $pub->categoria->Nombre_Categoria,
                    ] : null,
                    'vendedor' => [
                        'user_id' => $pub->vendedor->user_id,
                        'user' => [
                            'id' => $pub->vendedor->user->id,
                            'name' => $pub->vendedor->user->name,
                            'reputacionEstado' => $pub->vendedor->user->reputacionEstado ? [
                                'id' => $pub->vendedor->user->reputacionEstado->id,
                                'estado_actual' => $pub->vendedor->user->reputacionEstado->estado_actual,
                            ] : null,
                        ],
                    ],
                ];
            })
            ->values();

        return response()->json([
            'total' => count($publicaciones),
            'data' => $publicaciones,
        ]);
    }
}
