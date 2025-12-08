<?php

use Illuminate\Support\Facades\Route;

Route::get('/debug-publicaciones', function () {
    $publicaciones = \App\Models\Publicaciones::with([
        'vendedor.user.reputacionEstado',
        'categoria'
    ])
        ->where('estado', 'activa')
        ->get();

    return response()->json([
        'total' => $publicaciones->count(),
        'first' => $publicaciones->first() ? [
            'id' => $publicaciones->first()->id,
            'titulo' => $publicaciones->first()->Titulo_Publicacion,
            'vendedor_id' => $publicaciones->first()->vendedor?->user_id,
            'vendedor_name' => $publicaciones->first()->vendedor?->user?->name,
            'reputacion' => $publicaciones->first()->vendedor?->user?->reputacionEstado?->estado_actual,
        ] : null,
    ]);
});
