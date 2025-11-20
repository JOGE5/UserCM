<?php

use App\Http\Controllers\ChatController;
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
        // Si no existe la imagen en storage, devolver un placeholder público
        $placeholder = public_path('images/posters/university-logo.png');
        if (file_exists($placeholder)) {
            return response()->file($placeholder);
        }

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
        $userId = auth()->id();

        // Obtener publicaciones:
        // - Todas las 'activas' (de cualquier usuario)
        // - SOLO las activas del usuario actual (NO borradores)
        $publicaciones = \App\Models\Publicaciones::with('categoria', 'vendedor.user')
            ->where('estado', 'activa')
            ->get();

        return Inertia::render('Dashboard', [
            'publicaciones' => $publicaciones,
            'currentUserId' => $userId,
        ]);
    })->name('dashboard');

    // Rutas para completar perfil
    Route::get('/complete-profile', [ProfileController::class, 'showCompleteProfileForm'])->name('profile.complete.form');
    Route::post('/complete-profile', [ProfileController::class, 'complete'])->name('profile.complete');

    // Rutas de chats
    Route::get('/mensajes', [ChatController::class, 'index'])->name('mensajes.index');
    Route::post('/chats/private', [ChatController::class, 'createPrivateChat'])->name('chats.private.create');
    Route::get('/chats/{chat}', [ChatController::class, 'show'])->name('chats.show');
    Route::post('/chats/{chat}/messages', [ChatController::class, 'storeMessage'])->name('chats.messages.store');

    // Ruta de prueba para "Prueba" en el sidebar (ahora redirige a mensajes)
    Route::get('/prueba', function () {
        return redirect()->route('mensajes.index');
    })->name('algo'); // ← Este es el nombre que usas en tu sidebar

    // Rutas del sidebar -> Foros (productos)
    Route::get('/productos', [App\Http\Controllers\ForoController::class, 'index'])->name('productos');

    // Foros create/store/show/edit/update/destroy
    Route::get('/productos/create', [App\Http\Controllers\ForoController::class, 'create'])->name('productos.create');
    Route::post('/productos', [App\Http\Controllers\ForoController::class, 'store'])->name('productos.store');
    Route::get('/productos/{foro}', [App\Http\Controllers\ForoController::class, 'show'])->name('productos.show');
    Route::get('/productos/{foro}/edit', [App\Http\Controllers\ForoController::class, 'edit'])->name('productos.edit');
    Route::put('/productos/{foro}', [App\Http\Controllers\ForoController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{foro}', [App\Http\Controllers\ForoController::class, 'destroy'])->name('productos.destroy');
    // Comentarios de foros
    Route::post('/productos/{foro}/comentarios', [App\Http\Controllers\ComentarioController::class, 'store'])->name('productos.comentarios.store');

    Route::get('/borradores', function () {
        $userId = auth()->id();
        // Obtener solo los borradores del usuario actual
        $borradores = \App\Models\Publicaciones::with('categoria', 'vendedor.user')
            ->where('estado', 'borrador')
            ->whereHas('vendedor', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        return Inertia::render('Borradores', [
            'borradores' => $borradores,
            'currentUserId' => $userId,
        ]);
    })->name('borradores');

    // Rutas de Favoritos
    Route::get('/favoritos', [App\Http\Controllers\FavoritoController::class, 'index'])->name('favoritos.index');
    Route::post('/publicaciones/{publicacion}/favorito', [App\Http\Controllers\FavoritoController::class, 'toggle'])->name('favoritos.toggle');

    Route::get('/ajustes', function () {
        return Inertia::render('Ajustes/Index');
    })->name('ajustes');

    // Ruta para crear publicación
    Route::get('dashboard/create', function () {
        $categorias = \App\Models\Categorias::all();

        return Inertia::render('Create', ['categorias' => $categorias]);
    })->name('dashboard.create');

    // Ruta pública para servir imágenes de foros (similar a publicaciones)
    Route::get('/files/foros/{filename}', function ($filename) {
        $safe = basename($filename);
        $path = storage_path('app/public/foros/'.$safe);
        if (! file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    })->name('files.foros');

    // Rutas de publicaciones
    Route::post('/publicaciones', [PublicacionesController::class, 'store'])->name('publicaciones.store');
    // Rutas adicionales para operaciones sobre publicaciones (editar, mostrar, actualizar, eliminar)
    Route::get('/publicaciones/{publicaciones}/edit', [PublicacionesController::class, 'edit'])->name('publicaciones.edit');
    Route::get('/publicaciones/{publicaciones}', [PublicacionesController::class, 'show'])->name('publicaciones.show');
    Route::put('/publicaciones/{publicaciones}', [PublicacionesController::class, 'update'])->name('publicaciones.update');
    Route::delete('/publicaciones/{publicaciones}', [PublicacionesController::class, 'destroy'])->name('publicaciones.destroy');
    // Rutas para cambiar estado
    Route::patch('/publicaciones/{publicaciones}/draft', [PublicacionesController::class, 'toDraft'])->name('publicaciones.draft');
    Route::patch('/publicaciones/{publicaciones}/active', [PublicacionesController::class, 'toActive'])->name('publicaciones.active');
});
