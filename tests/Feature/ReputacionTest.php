<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ReputacionEntreUsuarios;
use App\Models\ReputacionEstado;
use App\Services\MarkovReputationService;
use Tests\TestCase;

class ReputacionTest extends TestCase
{
    /**
     * Test crear una calificación
     */
    public function test_crear_calificacion()
    {
        $usuario1 = User::factory()->create();
        $usuario2 = User::factory()->create();

        $calificacion = ReputacionEntreUsuarios::create([
            'ID_Usuario_Calificador' => $usuario1->id,
            'ID_Usuario_Calificado' => $usuario2->id,
            'Puntuacion' => 5,
            'Comentario' => 'Excelente vendedor',
        ]);

        $this->assertDatabaseHas('reputacion_entre_usuarios', [
            'ID_Reputacion' => $calificacion->ID_Reputacion,
            'Puntuacion' => 5,
        ]);
    }

    /**
     * Test actualizar estado de reputación
     */
    public function test_actualizar_estado_reputacion()
    {
        $usuario = User::factory()->create();
        $markovService = new MarkovReputationService();

        // Crear varias calificaciones
        for ($i = 0; $i < 5; $i++) {
            $calificador = User::factory()->create();
            ReputacionEntreUsuarios::create([
                'ID_Usuario_Calificador' => $calificador->id,
                'ID_Usuario_Calificado' => $usuario->id,
                'Puntuacion' => 4,
            ]);
        }

        // Actualizar estado
        $reputacionEstado = $markovService->actualizarEstado($usuario);

        $this->assertNotNull($reputacionEstado);
        /** @var string $estado */
        $estado = $reputacionEstado->estado_actual;
        $this->assertTrue(in_array($estado, ['Malo', 'Regular', 'Bueno', 'Excelente']));
    }

    /**
     * Test obtener promedio de puntuaciones
     */
    public function test_promedio_puntuaciones()
    {
        $usuario = User::factory()->create();
        $markovService = new MarkovReputationService();

        ReputacionEntreUsuarios::create([
            'ID_Usuario_Calificador' => User::factory()->create()->id,
            'ID_Usuario_Calificado' => $usuario->id,
            'Puntuacion' => 5,
        ]);

        ReputacionEntreUsuarios::create([
            'ID_Usuario_Calificador' => User::factory()->create()->id,
            'ID_Usuario_Calificado' => $usuario->id,
            'Puntuacion' => 3,
        ]);

        $promedio = $markovService->calcularPromedioCalificaciones($usuario);
        $this->assertEquals(4, $promedio);
    }

    /**
     * Test relaciones del modelo
     */
    public function test_relaciones_reputacion()
    {
        $usuario1 = User::factory()->create();
        $usuario2 = User::factory()->create();

        ReputacionEntreUsuarios::create([
            'ID_Usuario_Calificador' => $usuario1->id,
            'ID_Usuario_Calificado' => $usuario2->id,
            'Puntuacion' => 5,
        ]);

        // Verificar relación de reputaciones recibidas
        $this->assertCount(1, $usuario2->reputacionesRecibidas()->get());

        // Verificar relación de reputaciones emitidas
        $this->assertCount(1, $usuario1->reputacionesEmitidas()->get());
    }
}
