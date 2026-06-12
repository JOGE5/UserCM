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
    public function show(string $hash)
    {
        // Instanciar hashids (igual que en el Trait HasHashid)
        $hashids = new \Hashids\Hashids(config('app.key', 'campus-market-salt'), 8);
        $decoded = $hashids->decode($hash);
        
        $id = empty($decoded) ? abort(404) : $decoded[0];

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
                'hashid'            => $user->hashid,
                'name'              => $user->name,
                'profile_photo_url' => $user->profile_photo_url,
                'carrera'           => $perfil?->carrera?->Nombre_Carrera,
                'universidad'       => $perfil?->universidad?->Nombre_Universidad,
                'verificado'        => (bool) ($perfil?->verificado ?? false),
                'nivel'             => $perfil?->nivel ?? 1,
                'experiencia'       => $perfil?->experiencia ?? 0,
                'insignia'          => $perfil ? $perfil->insignia : 'Novato',
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
