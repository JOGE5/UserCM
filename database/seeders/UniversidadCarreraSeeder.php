<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Universidad;
use App\Models\Carrera;

class UniversidadCarreraSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'universidad' => [
                    'Nombre_Universidad'   => 'Universidad Mayor de San Andrés',
                    'Correo_Universidad'   => 'info@umsa.bo',
                    'Telefono_Universidad' => '+591 2 2440804',
                    'Direccion_Universidad'=> 'Av. Villazón N° 1995, La Paz, Bolivia',
                    'Sitio_Web'            => 'https://www.umsa.bo',
                    'Descripcion'          => 'Principal universidad pública del Estado Plurinacional de Bolivia, fundada en 1830.',
                    'Hora_apertura'        => '07:00:00',
                    'Hora_cierre'          => '21:00:00',
                ],
                'carreras' => [
                    ['nombre' => 'Medicina',                        'duracion' => '6 Años'],
                    ['nombre' => 'Odontología',                     'duracion' => '5 Años'],
                    ['nombre' => 'Farmacia y Bioquímica',           'duracion' => '5 Años'],
                    ['nombre' => 'Derecho',                         'duracion' => '5 Años'],
                    ['nombre' => 'Ingeniería Civil',                'duracion' => '5 Años'],
                    ['nombre' => 'Ingeniería Industrial',           'duracion' => '5 Años'],
                    ['nombre' => 'Ingeniería Informática',          'duracion' => '5 Años'],
                    ['nombre' => 'Ingeniería Eléctrica',            'duracion' => '5 Años'],
                    ['nombre' => 'Arquitectura y Urbanismo',        'duracion' => '5 Años'],
                    ['nombre' => 'Economía',                        'duracion' => '5 Años'],
                    ['nombre' => 'Contaduría Pública',              'duracion' => '5 Años'],
                    ['nombre' => 'Administración de Empresas',      'duracion' => '5 Años'],
                    ['nombre' => 'Comunicación Social',             'duracion' => '4 Años'],
                    ['nombre' => 'Psicología',                      'duracion' => '5 Años'],
                    ['nombre' => 'Trabajo Social',                  'duracion' => '4 Años'],
                    ['nombre' => 'Veterinaria y Zootecnia',         'duracion' => '5 Años'],
                    ['nombre' => 'Agronomía',                       'duracion' => '5 Años'],
                    ['nombre' => 'Biología',                        'duracion' => '5 Años'],
                    ['nombre' => 'Química',                         'duracion' => '5 Años'],
                    ['nombre' => 'Matemática',                      'duracion' => '5 Años'],
                ],
            ],
            [
                'universidad' => [
                    'Nombre_Universidad'   => 'Universidad Privada Franz Tamayo',
                    'Correo_Universidad'   => 'info@unifranz.edu.bo',
                    'Telefono_Universidad' => '+591 2 2910060',
                    'Direccion_Universidad'=> 'Av. Arce N° 2708, La Paz, Bolivia',
                    'Sitio_Web'            => 'https://www.unifranz.edu.bo',
                    'Descripcion'          => 'Universidad privada boliviana con sede principal en La Paz, reconocida por su enfoque tecnológico e innovador.',
                    'Hora_apertura'        => '07:30:00',
                    'Hora_cierre'          => '21:00:00',
                ],
                'carreras' => [
                    ['nombre' => 'Medicina',                        'duracion' => '6 Años'],
                    ['nombre' => 'Ingeniería en Sistemas',          'duracion' => '5 Años'],
                    ['nombre' => 'Administración de Empresas',      'duracion' => '4 Años'],
                    ['nombre' => 'Derecho',                         'duracion' => '5 Años'],
                    ['nombre' => 'Ingeniería Civil',                'duracion' => '5 Años'],
                    ['nombre' => 'Psicología',                      'duracion' => '5 Años'],
                    ['nombre' => 'Arquitectura',                    'duracion' => '5 Años'],
                    ['nombre' => 'Contaduría Pública',              'duracion' => '4 Años'],
                    ['nombre' => 'Diseño Gráfico',                  'duracion' => '4 Años'],
                    ['nombre' => 'Marketing y Publicidad',          'duracion' => '4 Años'],
                    ['nombre' => 'Ingeniería Comercial',            'duracion' => '4 Años'],
                    ['nombre' => 'Odontología',                     'duracion' => '5 Años'],
                ],
            ],
            [
                'universidad' => [
                    'Nombre_Universidad'   => 'Universidad Católica Boliviana San Pablo',
                    'Correo_Universidad'   => 'info@ucb.edu.bo',
                    'Telefono_Universidad' => '+591 2 2782222',
                    'Direccion_Universidad'=> 'Av. 14 de Septiembre N° 4807, La Paz, Bolivia',
                    'Sitio_Web'            => 'https://www.ucb.edu.bo',
                    'Descripcion'          => 'Universidad privada de inspiración cristiana, con presencia en las principales ciudades de Bolivia.',
                    'Hora_apertura'        => '07:00:00',
                    'Hora_cierre'          => '21:00:00',
                ],
                'carreras' => [
                    ['nombre' => 'Administración de Empresas',      'duracion' => '4 Años'],
                    ['nombre' => 'Economía',                        'duracion' => '4 Años'],
                    ['nombre' => 'Ingeniería Comercial',            'duracion' => '4 Años'],
                    ['nombre' => 'Ingeniería Civil',                'duracion' => '5 Años'],
                    ['nombre' => 'Ingeniería Informática y Sistemas','duracion' => '5 Años'],
                    ['nombre' => 'Derecho',                         'duracion' => '5 Años'],
                    ['nombre' => 'Arquitectura',                    'duracion' => '5 Años'],
                    ['nombre' => 'Comunicación Social',             'duracion' => '4 Años'],
                    ['nombre' => 'Psicología',                      'duracion' => '5 Años'],
                    ['nombre' => 'Medicina',                        'duracion' => '6 Años'],
                    ['nombre' => 'Ingeniería Electrónica',          'duracion' => '5 Años'],
                    ['nombre' => 'Filosofía',                       'duracion' => '4 Años'],
                ],
            ],
            [
                'universidad' => [
                    'Nombre_Universidad'   => 'Universidad de Aquino Bolivia',
                    'Correo_Universidad'   => 'info@udabol.edu.bo',
                    'Telefono_Universidad' => '+591 2 2430099',
                    'Direccion_Universidad'=> 'Av. Arce N° 2081, La Paz, Bolivia',
                    'Sitio_Web'            => 'https://www.udabol.edu.bo',
                    'Descripcion'          => 'Universidad privada boliviana con múltiples sedes a nivel nacional.',
                    'Hora_apertura'        => '07:00:00',
                    'Hora_cierre'          => '20:00:00',
                ],
                'carreras' => [
                    ['nombre' => 'Medicina',                        'duracion' => '6 Años'],
                    ['nombre' => 'Odontología',                     'duracion' => '5 Años'],
                    ['nombre' => 'Ingeniería en Sistemas',          'duracion' => '5 Años'],
                    ['nombre' => 'Administración de Empresas',      'duracion' => '4 Años'],
                    ['nombre' => 'Derecho',                         'duracion' => '5 Años'],
                    ['nombre' => 'Contaduría Pública',              'duracion' => '4 Años'],
                    ['nombre' => 'Ingeniería Civil',                'duracion' => '5 Años'],
                    ['nombre' => 'Psicología',                      'duracion' => '5 Años'],
                    ['nombre' => 'Enfermería',                      'duracion' => '4 Años'],
                ],
            ],
            [
                'universidad' => [
                    'Nombre_Universidad'   => 'Universidad Privada del Valle',
                    'Correo_Universidad'   => 'info@univalle.edu.bo',
                    'Telefono_Universidad' => '+591 2 2786860',
                    'Direccion_Universidad'=> 'Calle 21 de Calacoto N° 8261, La Paz, Bolivia',
                    'Sitio_Web'            => 'https://www.univalle.edu.bo',
                    'Descripcion'          => 'Universidad privada boliviana con enfoque en ciencias de la salud, exactas y sociales.',
                    'Hora_apertura'        => '07:30:00',
                    'Hora_cierre'          => '21:00:00',
                ],
                'carreras' => [
                    ['nombre' => 'Medicina',                        'duracion' => '6 Años'],
                    ['nombre' => 'Odontología',                     'duracion' => '5 Años'],
                    ['nombre' => 'Ingeniería en Sistemas',          'duracion' => '5 Años'],
                    ['nombre' => 'Administración de Empresas',      'duracion' => '4 Años'],
                    ['nombre' => 'Derecho',                         'duracion' => '5 Años'],
                    ['nombre' => 'Arquitectura',                    'duracion' => '5 Años'],
                    ['nombre' => 'Ingeniería Civil',                'duracion' => '5 Años'],
                    ['nombre' => 'Comunicación Social',             'duracion' => '4 Años'],
                    ['nombre' => 'Psicología',                      'duracion' => '5 Años'],
                ],
            ],
            [
                'universidad' => [
                    'Nombre_Universidad'   => 'Universidad Real',
                    'Correo_Universidad'   => 'info@ureal.edu.bo',
                    'Telefono_Universidad' => '+591 2 2774411',
                    'Direccion_Universidad'=> 'Av. Ballivián N° 1491, La Paz, Bolivia',
                    'Sitio_Web'            => 'https://www.ureal.edu.bo',
                    'Descripcion'          => 'Universidad privada boliviana orientada a la formación de profesionales con valores.',
                    'Hora_apertura'        => '08:00:00',
                    'Hora_cierre'          => '20:00:00',
                ],
                'carreras' => [
                    ['nombre' => 'Administración de Empresas',      'duracion' => '4 Años'],
                    ['nombre' => 'Ingeniería en Sistemas',          'duracion' => '5 Años'],
                    ['nombre' => 'Derecho',                         'duracion' => '5 Años'],
                    ['nombre' => 'Contaduría Pública',              'duracion' => '4 Años'],
                    ['nombre' => 'Ingeniería Civil',                'duracion' => '5 Años'],
                    ['nombre' => 'Psicología',                      'duracion' => '5 Años'],
                    ['nombre' => 'Comunicación Social',             'duracion' => '4 Años'],
                ],
            ],
            [
                'universidad' => [
                    'Nombre_Universidad'   => 'Universidad Salesiana de Bolivia',
                    'Correo_Universidad'   => 'info@usalesiana.edu.bo',
                    'Telefono_Universidad' => '+591 2 2786620',
                    'Direccion_Universidad'=> 'Calle Colón esq. Ballivián, La Paz, Bolivia',
                    'Sitio_Web'            => 'https://www.usalesiana.edu.bo',
                    'Descripcion'          => 'Universidad privada de inspiración salesiana, con enfoque en la formación integral de jóvenes.',
                    'Hora_apertura'        => '07:00:00',
                    'Hora_cierre'          => '21:00:00',
                ],
                'carreras' => [
                    ['nombre' => 'Ingeniería en Sistemas',          'duracion' => '5 Años'],
                    ['nombre' => 'Ingeniería Civil',                'duracion' => '5 Años'],
                    ['nombre' => 'Administración de Empresas',      'duracion' => '4 Años'],
                    ['nombre' => 'Contaduría Pública',              'duracion' => '4 Años'],
                    ['nombre' => 'Comunicación Social',             'duracion' => '4 Años'],
                    ['nombre' => 'Trabajo Social',                  'duracion' => '4 Años'],
                    ['nombre' => 'Psicología',                      'duracion' => '5 Años'],
                    ['nombre' => 'Filosofía y Pedagogía',           'duracion' => '4 Años'],
                ],
            ],
        ];

        foreach ($data as $item) {
            $universidad = Universidad::firstOrCreate(
                ['Nombre_Universidad' => $item['universidad']['Nombre_Universidad']],
                $item['universidad']
            );

            foreach ($item['carreras'] as $carrera) {
                Carrera::firstOrCreate(
                    [
                        'Nombre_Carrera'  => $carrera['nombre'],
                        'Cod_Universidad' => $universidad->Cod_Universidad,
                    ],
                    [
                        'Descripcion_Carrera' => 'Carrera de ' . $carrera['nombre'],
                        'Duracion_Carrera'    => $carrera['duracion'],
                    ]
                );
            }
        }
    }
}
