<?php

use App\Http\Controllers\Api\CarreraController;
use App\Http\Controllers\Api\ReputacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/carreras', [CarreraController::class, 'index']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profanities', function (Request $request) {
        return response()->json(config('profanities'));
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rutas de reputación
    Route::post('/reputacion/{id}', [ReputacionController::class, 'store']);
    Route::get('/reputacion/{id}', [ReputacionController::class, 'show']);

    // Publicaciones ordenadas por reputación
    Route::get('/publicaciones', [ReputacionController::class, 'publicacionesOrdenadas']);
});
