<?php

namespace App\Http\Controllers;

use App\Models\Favorito;
use App\Models\Publicaciones;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FavoritoController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $favoritos = Favorito::where('user_id', $userId)
            ->with('publicacion.categoria', 'publicacion.vendedor.user')
            ->get();

        $publicacionesFavoritas = $favoritos->pluck('publicacion');

        return Inertia::render('Favoritos', [
            'publicaciones' => $publicacionesFavoritas,
            'currentUserId' => $userId,
        ]);
    }

    public function toggle(Request $request, Publicaciones $publicacion)
    {
        $userId = auth()->id();

        // No permitir que un usuario marque su propia publicación como favorito
        try {
            if ($publicacion->vendedor && $publicacion->vendedor->user_id === $userId) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => 'No puedes marcar tu propia publicación como favorita.'], 403);
                }
                return back()->with('error', 'No puedes marcar tu propia publicación como favorita.');
            }
        } catch (\Throwable $e) {
            // en caso de cualquier problema con la relación, seguir con la lógica (pero no bloquear)
        }

        $existe = Favorito::where('user_id', $userId)
            ->where('publicacion_id', $publicacion->id)
            ->exists();

        if ($existe) {
            Favorito::where('user_id', $userId)
                ->where('publicacion_id', $publicacion->id)
                ->delete();
            $mensaje = 'Eliminado de favoritos.';
            $esFavorito = false;
        } else {
            Favorito::create([
                'user_id' => $userId,
                'publicacion_id' => $publicacion->id,
            ]);
            $mensaje = 'Añadido a favoritos.';
            $esFavorito = true;
        }

        return back()->with('success', $mensaje);
    }

    public function esFavorito(Publicaciones $publicacion)
    {
        $userId = auth()->id();
        $esFavorito = Favorito::where('user_id', $userId)
            ->where('publicacion_id', $publicacion->id)
            ->exists();

        return response()->json(['esFavorito' => $esFavorito]);
    }
}
