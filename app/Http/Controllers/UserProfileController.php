<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Publicaciones;
use App\Models\ReputacionEntreUsuarios;
use App\Services\MarkovReputationService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserProfileController extends Controller
{
    public function show(int $id)
    {
        $user = User::with([
            'usuarioCampusMarket.carrera',
            'usuarioCampusMarket.universidad',
            'reputacionEstado',
        ])->findOrFail($id);

        $perfil = $user->usuarioCampusMarket;

        $markov  = new MarkovReputationService();
        $promedio = $markov->calcularPromedioCalificaciones($user);
        $total    = ReputacionEntreUsuarios::where('ID_Usuario_Calificado', $user->id)->count();

        $yaCalifico = Auth::check()
            ? ReputacionEntreUsuarios::where('ID_Usuario_Calificador', Auth::id())
                ->where('ID_Usuario_Calificado', $user->id)
                ->exists()
            : false;

        $publicaciones = Publicaciones::with('categoria')
            ->whereHas('vendedor', fn ($q) => $q->where('user_id', $user->id))
            ->where('estado', 'activa')
            ->latest()
            ->take(12)
            ->get();

        return Inertia::render('Perfil/Vendedor', [
            'vendedor' => [
                'id'                => $user->id,
                'name'              => $user->name,
                'profile_photo_url' => $user->profile_photo_url,
                'carrera'           => $perfil?->carrera?->Nombre_Carrera,
                'universidad'       => $perfil?->universidad?->Nombre_Universidad,
                'verificado'        => (bool) ($perfil?->verificado ?? false),
            ],
            'reputacion'           => $user->reputacionEstado,
            'promedio'             => $promedio !== null ? round((float) $promedio, 1) : null,
            'total_calificaciones' => $total,
            'publicaciones'        => $publicaciones,
            'isOwner'              => Auth::id() === $user->id,
            'yaCalifico'           => $yaCalifico,
            'currentUserId'        => Auth::id(),
        ]);
    }
}
