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
     * Aplica la matriz de transición
     */
    public function aplicarTransicion(string $estadoActual): array
    {
        $estadoIndex = array_search($estadoActual, self::STATES);

        if ($estadoIndex === false) {
            $estadoIndex = 1; // Regular por defecto
        }

        $transiciones = self::TRANSITION_MATRIX[$estadoIndex];

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

        $probabilidades = $this->aplicarTransicion($estadoBase);
        $estadoNuevo = $this->obtenerEstadoMayorProbabilidad($probabilidades);

        $reputacion = ReputacionEstadoUsuario::updateOrCreate(
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
    public function obtenerEstado(User $user): ?ReputacionEstadoUsuario
    {
        /** @var int $userId */
        $userId = $user->id;
        
        return ReputacionEstadoUsuario::where('user_id', $userId)->first();
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
