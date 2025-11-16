<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicacionesController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Ruta pública para servir imágenes de publicaciones (evita problemas con symlinks en Windows)
Route::get('/files/publicaciones/{filename}', function ($filename) {
    $safe = basename($filename);
    $path = storage_path('app/public/publicaciones/'.$safe);
    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->name('files.publicaciones');

// Ruta pública para servir imágenes de perfil de usuarios
Route::get('/files/perfil/{filename}', function ($filename) {
    $safe = basename($filename);
    $path = storage_path('app/public/perfil/'.$safe);
    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->name('files.perfil');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $publicaciones = \App\Models\Publicaciones::with('categoria', 'vendedor.user')->get();

        return Inertia::render('Dashboard', ['publicaciones' => $publicaciones]);
    })->name('dashboard');

    // Rutas para completar perfil
    Route::get('/complete-profile', [ProfileController::class, 'showCompleteForm'])->name('profile.complete.form');
    Route::post('/complete-profile', [ProfileController::class, 'complete'])->name('profile.complete');

    // Ruta de prueba para "Prueba" en el sidebar
    Route::get('/prueba', function () {
        return Inertia::render('Prueba/Index'); // Vista: resources/js/Pages/Prueba/Index.vue
    })->name('algo'); // ← Este es el nombre que usas en tu sidebar

    // Rutas del sidebar
    Route::get('/productos', function () {
        return Inertia::render('Productos/Index');
    })->name('productos');

    Route::get('/roles', function () {
        return Inertia::render('Roles/Index');
    })->name('roles');

    Route::get('/ajustes', function () {
        return Inertia::render('Ajustes/Index');
    })->name('ajustes');

    // Ruta para crear publicación
    Route::get('dashboard/create', function () {
        $categorias = \App\Models\Categorias::all();

        return Inertia::render('Create', ['categorias' => $categorias]);
    })->name('dashboard.create');

    // Rutas de publicaciones
    Route::post('/publicaciones', [PublicacionesController::class, 'store'])->name('publicaciones.store');
});
