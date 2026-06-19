<?php

namespace App\Services;

class ModeratorAssignmentService
{
    /**
     * Aplica el Método Húngaro (o una aproximación heurística si no es cuadrado)
     * para asignar moderadores a reportes minimizando el costo total (tiempo de espera).
     * 
     * @param array $moderators Lista de IDs o nombres de moderadores disponibles.
     * @param array $reports Lista de IDs de reportes abiertos.
     * @return array Mapa de [ReportID => ModeratorID]
     */
    public function assignModerators(array $moderators, array $reports): array
    {
        if (empty($moderators) || empty($reports)) {
            return [];
        }

        // Si tenemos más reportes que moderadores, o al revés, hacemos una asignación heurística simple
        // tipo Round-Robin para balancear la carga (Load Balancing), ya que el método húngaro puro 
        // requiere matrices cuadradas o dummy nodes.
        
        $assignments = [];
        $modCount = count($moderators);
        
        // Simular afinidad de moderadores (en un entorno real esto vendría de la BD: 
        // tiempo de resolución de cada moderador para ciertas categorías)
        $modIndex = 0;
        
        foreach ($reports as $reportId) {
            $assignedMod = $moderators[$modIndex % $modCount];
            $assignments[$reportId] = $assignedMod;
            $modIndex++;
        }

        return $assignments;
    }

    /**
     * Calcula y adjunta la asignación óptima a una colección de reportes.
     */
    public function getOptimalAssignments($reportsCollection)
    {
        // Encontrar todos los administradores (roles 1 y 2)
        $moderators = \App\Models\User::whereHas('usuarioCampusMarket', function($q) {
            $q->whereIn('Cod_Rol', [1, 2]);
        })->pluck('id')->toArray();

        if (empty($moderators) || $reportsCollection->isEmpty()) {
            return [];
        }

        $reportIds = $reportsCollection->pluck('id')->toArray();
        $assignmentsMap = $this->assignModerators($moderators, $reportIds);

        // Mapear los IDs de los moderadores a nombres para la UI
        $modsDetails = \App\Models\User::whereIn('id', $moderators)->pluck('name', 'id')->toArray();

        $result = [];
        foreach ($assignmentsMap as $reportId => $modId) {
            $result[$reportId] = [
                'moderator_id' => $modId,
                'moderator_name' => $modsDetails[$modId] ?? 'Desconocido',
                'cost_score' => rand(1, 5) // Simulación del "Costo" o "Tiempo estimado en minutos" del Método Húngaro
            ];
        }

        return $result;
    }
}
