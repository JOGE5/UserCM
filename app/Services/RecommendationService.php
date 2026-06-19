<?php

namespace App\Services;

use App\Models\Publicaciones;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * Algoritmo de Recomendación Híbrido (Heurístico y Similitud).
     * Recomienda productos al usuario basándose en su Carrera, Universidad
     * y el historial de compras/reportes, priorizando a vendedores con "Excelente" reputación (Markov).
     */
    public function getRecommendationsForUser(User $user, int $limit = 6)
    {
        $perfil = $user->usuarioCampusMarket;
        
        if (!$perfil) {
            // Si el usuario no tiene perfil (ej. admin o incompleto), retornar publicaciones populares generales.
            return Publicaciones::with(['vendedor.user', 'categoria'])
                ->where('estado', 'activa')
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        $codCarrera = $perfil->Cod_Carrera;
        $codUniversidad = $perfil->Cod_Universidad;

        // Construir consulta base
        $query = DB::table('publicaciones')
            ->join('usuarios_campus_markets', 'publicaciones.ID_Vendedor', '=', 'usuarios_campus_markets.id')
            ->leftJoin('reputacion_estado', 'usuarios_campus_markets.user_id', '=', 'reputacion_estado.user_id')
            ->select(
                'publicaciones.id',
                'publicaciones.Titulo_Publicacion',
                'publicaciones.Precio_Publicacion',
                'publicaciones.imgen',
                'publicaciones.ID_Categoria',
                'usuarios_campus_markets.Cod_Carrera as Vendedor_Carrera',
                'usuarios_campus_markets.Cod_Universidad as Vendedor_Universidad',
                'reputacion_estado.estado_actual'
            )
            ->where('publicaciones.estado', 'activa')
            ->where('usuarios_campus_markets.user_id', '!=', $user->id); // No recomendar sus propios productos

        $publicacionesRaw = $query->get();

        // -----------------------------------------------------
        // ALGORITMO DE PUNTUACIÓN (SCORING)
        // -----------------------------------------------------
        $scoredProducts = $publicacionesRaw->map(function ($pub) use ($codCarrera, $codUniversidad) {
            $score = 0;

            // 1. Similitud por Carrera (+50 puntos)
            if ($pub->Vendedor_Carrera === $codCarrera) {
                $score += 50;
            }

            // 2. Similitud por Universidad (+30 puntos)
            if ($pub->Vendedor_Universidad === $codUniversidad) {
                $score += 30;
            }

            // 3. Cadena de Markov: Reputación del Vendedor
            // Premiar estados absorbentes positivos (Excelente)
            switch ($pub->estado_actual) {
                case 'Excelente':
                    $score += 40;
                    break;
                case 'Bueno':
                    $score += 20;
                    break;
                case 'Regular':
                    $score += 0;
                    break;
                case 'Malo':
                    $score -= 50; // Penalización severa, baja en el ranking
                    break;
                default:
                    $score += 5; // Sin estado definido, puntuación neutral
            }

            $pub->relevance_score = $score;
            return $pub;
        });

        // Ordenar por score descendente y tomar el límite
        $topIds = $scoredProducts->sortByDesc('relevance_score')
            ->take($limit)
            ->pluck('id');

        // Retornar los modelos Eloquent correspondientes a los IDs top, manteniendo el orden
        if ($topIds->isEmpty()) {
            return collect([]);
        }

        $idsString = $topIds->implode(',');

        return Publicaciones::with(['vendedor.user', 'categoria'])
            ->whereIn('id', $topIds)
            ->orderByRaw("FIELD(id, $idsString)")
            ->get();
    }
}
