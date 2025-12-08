<?php

use App\Http\Controllers\Api\CarreraController;
use App\Http\Controllers\Api\ReputacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/profanities', function (Request $request) {
    return response()->json(config('profanities'));
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/carreras', [CarreraController::class, 'index']);

// Rutas de reputación - Autenticadas (web o sanctum)
Route::middleware(['auth:web,sanctum'])->group(function () {
    Route::post('/reputacion/{id}', [ReputacionController::class, 'store']);
    Route::get('/reputacion/{id}', [ReputacionController::class, 'show']);
});

// Publicaciones ordenadas por reputación - Sin autenticación
Route::get('/publicaciones', [ReputacionController::class, 'publicacionesOrdenadas']);
