<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForecastService
{
    /**
     * Aplica Suavizado Exponencial Simple (SES) para predecir la cantidad
     * de publicaciones nuevas o interacciones para la próxima semana.
     *
     * Fórmula SES: F_{t+1} = \alpha * Y_t + (1 - \alpha) * F_t
     * Donde:
     * - F_{t+1} es el pronóstico para el siguiente período
     * - \alpha es el factor de suavizado (0 < \alpha < 1)
     * - Y_t es el valor real en el período actual
     * - F_t es el pronóstico previo
     */
    public function predictNextWeekPublications(float $alpha = 0.3): array
    {
        // Obtener historial de las últimas 8 semanas (agrupado por semana)
        // YEARWEEK() es de MySQL; en PostgreSQL usamos to_char(...,'IYYYIW') = año+semana ISO.
        $semanaExpr = DB::connection()->getDriverName() === 'pgsql'
            ? "to_char(created_at, 'IYYYIW')"
            : 'YEARWEEK(created_at, 1)';

        $historicalData = DB::table('publicaciones')
            ->select(DB::raw("{$semanaExpr} as semana"), DB::raw('count(*) as total'))
            ->where('created_at', '>=', Carbon::now()->subWeeks(8))
            ->groupBy('semana')
            ->orderBy('semana', 'asc')
            ->get();

        if ($historicalData->count() < 2) {
            return [
                'prediction' => 0,
                'trend' => 'insufficient_data',
                'historical' => []
            ];
        }

        $forecasts = [];
        $actuals = [];

        // Inicializar el primer pronóstico con el primer valor real (método simple)
        $firstActual = $historicalData->first()->total;
        $forecasts[] = $firstActual;
        $actuals[] = $firstActual;

        // Calcular SES para la serie histórica
        $lastForecast = $firstActual;
        
        foreach ($historicalData->skip(1) as $index => $data) {
            $actual = $data->total;
            $actuals[] = $actual;
            
            // F_{t+1} = \alpha * Y_t + (1 - \alpha) * F_t
            $nextForecast = ($alpha * $actual) + ((1 - $alpha) * $lastForecast);
            $forecasts[] = round($nextForecast, 2);
            $lastForecast = $nextForecast;
        }

        // Predicción futura (Próxima semana)
        // El último valor de $nextForecast es justamente el pronóstico para T+1
        $futurePrediction = round($lastForecast);

        // Determinar tendencia comparando la predicción futura con la última semana real
        $lastActual = end($actuals);
        if ($futurePrediction > $lastActual) {
            $trend = 'up';
        } elseif ($futurePrediction < $lastActual) {
            $trend = 'down';
        } else {
            $trend = 'stable';
        }

        return [
            'prediction' => max(0, $futurePrediction), // Evitar valores negativos
            'trend' => $trend,
            'historical_actuals' => $actuals,
            'historical_forecasts' => $forecasts,
        ];
    }
}
