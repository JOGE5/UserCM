<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReputacionEstado;
use Illuminate\Support\Facades\DB;

class MarkovReputationService
{
    /**
     * Estados posibles
     */
    private const STATES = ['Malo', 'Regular', 'Bueno', 'Excelente'];

    /**
     * Matriz de transición 4x4
     * Filas: Malo, Regular, Bueno, Excelente
     * Columnas: [Malo, Regular, Bueno, Excelente]
     */
    private const TRANSITION_MATRIX = [
        [0.40, 0.40, 0.15, 0.05],  // Desde Malo
        [0.10, 0.50, 0.30, 0.10],  // Desde Regular
        [0.05, 0.15, 0.60, 0.20],  // Desde Bueno
        [0.02, 0.08, 0.25, 0.65],  // Desde Excelente
    ];

    /**
     * Calcula el promedio de puntuaciones para un usuario
     */
    public function calcularPromedioCalificaciones(User $user): ?float
    {
        /** @var int $userId */
        $userId = $user->id;
        
        /** @var float|null $promedio */
        $promedio = DB::table('reputacion_entre_usuarios')
            ->where('ID_Usuario_Calificado', $userId)
            ->avg('Puntuacion');

        return $promedio;
    }

    /**
     * Determina el estado base según el promedio
     */
    public function determinarEstadoPorPromedio(float $promedio): string
    {
        if ($promedio < 2) {
            return 'Malo';
        } elseif ($promedio < 3) {
            return 'Regular';
        } elseif ($promedio < 4) {
            return 'Bueno';
        } else {
            return 'Excelente';
        }
    }

    /**
     * Aplica la matriz de transición dinámicamente basándose en el comportamiento real del usuario (Gamificación).
     */
    public function aplicarTransicion(User $user, string $estadoActual): array
    {
        $estadoIndex = array_search($estadoActual, self::STATES);

        if ($estadoIndex === false) {
            $estadoIndex = 1; // Regular por defecto
        }

        $transiciones = self::TRANSITION_MATRIX[$estadoIndex];

        // Lógica Dinámica de Markov (Gamificación)
        $perfil = $user->usuarioCampusMarket;
        $xp = $perfil ? $perfil->experiencia : 0;
        
        // Obtener reportes asociados a publicaciones del usuario (simulado si no hay relación directa)
        $reportsCount = DB::table('reportsPubli')
            ->where('reportable_type', \App\Models\Publicaciones::class)
            ->whereIn('reportable_id', function($q) use ($perfil) {
                if ($perfil) {
                    $q->select('id')->from('publicaciones')->where('ID_Vendedor', $perfil->id);
                } else {
                    $q->select('id')->from('publicaciones')->where('ID_Vendedor', 0);
                }
            })
            ->count();

        // Si tiene muchos reportes, penalizar empujando la probabilidad hacia "Malo" o "Regular"
        if ($reportsCount > 0) {
            $penalizacion = min(0.30, $reportsCount * 0.10); // Máximo 30% de penalización
            $transiciones[0] += $penalizacion; // Aumentar "Malo"
            $transiciones[2] = max(0, $transiciones[2] - ($penalizacion / 2)); // Reducir "Bueno"
            $transiciones[3] = max(0, $transiciones[3] - ($penalizacion / 2)); // Reducir "Excelente"
        }

        // Si tiene mucha experiencia (Gamificación alta), premiar empujando probabilidad a "Bueno" o "Excelente"
        if ($xp > 100) {
            $bonificacion = min(0.20, floor($xp / 100) * 0.05); // +5% por nivel, max 20%
            $transiciones[3] += $bonificacion; // Aumentar "Excelente"
            $transiciones[2] += ($bonificacion / 2); // Aumentar "Bueno"
            $transiciones[0] = max(0, $transiciones[0] - $bonificacion); // Reducir "Malo"
            $transiciones[1] = max(0, $transiciones[1] - ($bonificacion / 2)); // Reducir "Regular"
        }

        // Normalizar para asegurar que sumen exactamente 1.0 (100%)
        $suma = array_sum($transiciones);
        if ($suma > 0) {
            $transiciones = array_map(function($val) use ($suma) { return $val / $suma; }, $transiciones);
        }

        return [
            'p_malo' => $transiciones[0],
            'p_regular' => $transiciones[1],
            'p_bueno' => $transiciones[2],
            'p_excelente' => $transiciones[3],
        ];
    }

    /**
     * Obtiene el estado con mayor probabilidad
     */
    public function obtenerEstadoMayorProbabilidad(array $probabilidades): string
    {
        $maxProb = 0;
        $estado = 'Regular';

        foreach ($probabilidades as $key => $prob) {
            if ($prob > $maxProb) {
                $maxProb = $prob;
                $estado = match ($key) {
                    'p_malo' => 'Malo',
                    'p_regular' => 'Regular',
                    'p_bueno' => 'Bueno',
                    'p_excelente' => 'Excelente',
                    default => 'Regular',
                };
            }
        }

        return $estado;
    }

    /**
     * Actualiza el estado de reputación del usuario
     */
    public function actualizarEstado(User $user): ReputacionEstado
    {
        /** @var int $userId */
        $userId = $user->id;

        $promedio = $this->calcularPromedioCalificaciones($user);
        $estadoBase = 'Regular';

        if ($promedio !== null && $promedio > 0) {
            $estadoBase = $this->determinarEstadoPorPromedio($promedio);
        }

        $probabilidades = $this->aplicarTransicion($user, $estadoBase);
        $estadoNuevo = $this->obtenerEstadoMayorProbabilidad($probabilidades);

        $reputacion = ReputacionEstado::updateOrCreate(
            ['user_id' => $userId],
            [
                'estado_actual' => $estadoNuevo,
                'p_malo' => $probabilidades['p_malo'],
                'p_regular' => $probabilidades['p_regular'],
                'p_bueno' => $probabilidades['p_bueno'],
                'p_excelente' => $probabilidades['p_excelente'],
            ]
        );

        return $reputacion;
    }

    /**
     * Obtiene el estado de reputación
     */
    public function obtenerEstado(User $user): ?ReputacionEstado
    {
        /** @var int $userId */
        $userId = $user->id;
        
        return ReputacionEstado::where('user_id', $userId)->first();
    }

    /**
     * Obtiene el valor ordinal del estado (para ordenamiento)
     */
    public function obtenerOrdinalEstado(string $estado): int
    {
        return match ($estado) {
            'Malo' => 1,
            'Regular' => 2,
            'Bueno' => 3,
            'Excelente' => 4,
            default => 0,
        };
    }
}
