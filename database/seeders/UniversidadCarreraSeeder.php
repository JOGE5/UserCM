<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Universidad;
use App\Models\Carrera;

class UniversidadCarreraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear la universidad
        $universidad = Universidad::firstOrCreate(
            ['Nombre_Universidad' => 'Universidad Privada Franz Tamayo'],
            [
                'Correo_Universidad' => 'info@unifranz.edu.bo',
                'Descripcion' => 'Sede principal de UNIFRANZ.',
                'Direccion_Universidad' => 'Bolivia',
            ]
        );

        // Crear las carreras asociadas
        $carreras = [
            'Medicina',
            'Ingenieria en Sistemas',
            'Administracion de Empresas'
        ];

        foreach ($carreras as $carreraNombre) {
            Carrera::firstOrCreate(
                [
                    'Nombre_Carrera' => $carreraNombre,
                    'Cod_Universidad' => $universidad->Cod_Universidad,
                ],
                [
                    'Descripcion_Carrera' => 'Carrera de ' . $carreraNombre,
                    'Duracion_Carrera' => '5 Años',
                ]
            );
        }
    }
}
