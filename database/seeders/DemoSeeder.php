<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UsuarioCampusMarket;
use App\Services\MarkovReputationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Datos demo para poblar el admin: usuarios, reputación, publicaciones y reportes.
 * Ejecutar con: php artisan db:seed --class=DemoSeeder
 * (Pensado para correr una vez; reejecutar duplica publicaciones/reportes.)
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $carreras = DB::table('carreras')->get();
        $categorias = DB::table('categorias_articulos')->pluck('Cod_Categoria')->all();

        // ---------------------------------------------------------------
        // 1. USUARIOS (estudiantes con perfil completo)
        // ---------------------------------------------------------------
        // Cada estudiante apunta a un "bucket" de reputación para tener variedad
        // en los 4 estados de Markov (Malo / Regular / Bueno / Excelente).
        $estudiantes = [
            ['Maria',   'Quispe',   'Femenino',  'Excelente', 320],
            ['Carlos',  'Mamani',   'Masculino', 'Excelente', 260],
            ['Lucia',   'Flores',   'Femenino',  'Excelente', 180],
            ['Diego',   'Choque',   'Masculino', 'Bueno',     140],
            ['Ana',     'Condori',  'Femenino',  'Bueno',     110],
            ['Jorge',   'Apaza',    'Masculino', 'Bueno',      60],
            ['Sofia',   'Vargas',   'Femenino',  'Regular',    40],
            ['Pedro',   'Rojas',    'Masculino', 'Regular',    20],
            ['Valeria', 'Gutierrez','Femenino',  'Regular',     0],
            ['Luis',    'Ticona',   'Masculino', 'Malo',        0],
            ['Camila',  'Huanca',   'Femenino',  'Malo',        0],
            ['Andres',  'Cruz',     'Masculino', 'Excelente', 240],
        ];

        $patron = [
            'Excelente' => [5, 5, 4],
            'Bueno'     => [4, 3, 4],
            'Regular'   => [3, 2, 3],
            'Malo'      => [2, 1, 1],
        ];

        $creados = [];
        foreach ($estudiantes as $i => [$nombre, $apellido, $genero, $bucket, $xp]) {
            $carrera = $carreras->random();

            $user = User::updateOrCreate(
                ['email' => strtolower($nombre.'.'.$apellido.($i + 1).'@campus.test')],
                [
                    'name' => "$nombre $apellido",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $perfil = UsuarioCampusMarket::firstOrNew(['user_id' => $user->id]);
            $perfil->Apellidos = $apellido;
            $perfil->Genero = $genero;
            $perfil->Estado = 'Activo';
            $perfil->Telefono = '7' . rand(1000000, 9999999);
            $perfil->Cod_Rol = 3; // Estudiante
            $perfil->Cod_Carrera = $carrera->Cod_Carrera;
            $perfil->Cod_Universidad = $carrera->Cod_Universidad;
            $perfil->verificado = (bool) rand(0, 1);
            $perfil->experiencia = $xp;
            $perfil->nivel = max(1, intdiv($xp, 100) + 1);
            $perfil->save();

            $creados[] = ['user' => $user, 'perfil' => $perfil, 'bucket' => $bucket];
        }

        // ---------------------------------------------------------------
        // 2. REPUTACIÓN ENTRE USUARIOS (calificaciones 1-5)
        // ---------------------------------------------------------------
        $comentarios = [
            'Buen vendedor, todo en orden.', 'Entrega puntual.', 'Producto tal cual la foto.',
            'Demoró un poco pero bien.', 'No respondió a tiempo.', 'Excelente trato.',
            'Recomendado.', 'El producto tenía detalles.', 'Todo perfecto.', 'Regular nomás.',
        ];

        foreach ($creados as $idx => $c) {
            $calificado = $c['user']->id;
            $notas = $patron[$c['bucket']];

            // 3 calificadores distintos (que no sean el mismo usuario)
            $calificadores = collect($creados)
                ->reject(fn ($o) => $o['user']->id === $calificado)
                ->shuffle()
                ->take(count($notas));

            foreach ($calificadores->values() as $j => $o) {
                DB::table('reputacion_entre_usuarios')->updateOrInsert(
                    [
                        'ID_Usuario_Calificador' => $o['user']->id,
                        'ID_Usuario_Calificado' => $calificado,
                    ],
                    [
                        'Puntuacion' => $notas[$j],
                        'Comentario' => $comentarios[array_rand($comentarios)],
                        'created_at' => now()->subDays(rand(0, 25)),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // ---------------------------------------------------------------
        // 3. PUBLICACIONES
        // ---------------------------------------------------------------
        $productos = [
            ['Laptop Lenovo ThinkPad', 1800], ['Calculadora Casio fx-991', 120],
            ['Libro Cálculo de Stewart', 90], ['Mochila para laptop', 150],
            ['Audífonos Bluetooth', 200], ['Mouse inalámbrico', 80],
            ['Bata de laboratorio', 70], ['Tabla de dibujo técnico', 110],
            ['Microscopio de práctica', 950], ['Juego de escuadras', 35],
            ['Disco SSD 480GB', 320], ['Monitor 24"', 700],
            ['Set de marcadores', 45], ['Estetoscopio Littmann', 600],
            ['Arduino Uno + kit', 250], ['Libro Anatomía Rouvière', 280],
            ['Silla ergonómica', 480], ['Memoria USB 64GB', 60],
            ['Teclado mecánico', 280], ['Maletín ejecutivo', 220],
            ['Cámara web HD', 130], ['Power bank 20000mAh', 160],
        ];
        $estados = ['activa', 'activa', 'activa', 'activa', 'vendida', 'borrador', 'oculta'];
        $condiciones = ['nuevo', 'usado'];

        $pubIds = [];
        foreach ($productos as $k => [$titulo, $precio]) {
            $vendedor = $creados[array_rand($creados)]['perfil'];
            $estado = $estados[array_rand($estados)];

            $pubIds[] = DB::table('publicaciones')->insertGetId([
                'Titulo_Publicacion' => $titulo,
                'Descripcion_Publicacion' => 'Producto en buen estado, ideal para estudiantes. Coordinar entrega en campus.',
                'Precio_Publicacion' => $precio + rand(-10, 30),
                'Cod_Categoria' => $categorias[array_rand($categorias)],
                'ID_Vendedor' => $vendedor->id,
                'estado' => $estado,
                'Estado_Publicacion' => $estado === 'activa' ? 1 : 0,
                'condicion_producto' => $condiciones[array_rand($condiciones)],
                'ubicacion' => 'La Paz',
                'Vistas_Publicacion' => rand(0, 350),
                'vendido_at' => $estado === 'vendida' ? now()->subDays(rand(0, 10)) : null,
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now(),
            ]);
        }

        // ---------------------------------------------------------------
        // 4. REPORTES (cola de moderación)
        // ---------------------------------------------------------------
        $urgentes = ['Contenido ofensivo', 'Acoso al vendedor', 'Insultos en la descripción', 'Imagen explícita'];
        $normales = ['Producto duplicado', 'Posible estafa', 'Precio sospechoso (spam)', 'Categoría incorrecta', 'Información engañosa'];

        for ($k = 0; $k < 36; $k++) {
            $urgente = rand(0, 1) === 1;
            DB::table('reportsPubli')->insert([
                'reportable_type' => \App\Models\Publicaciones::class,
                'reportable_id' => $pubIds[array_rand($pubIds)],
                'reporter_id' => $creados[array_rand($creados)]['user']->id,
                'reason' => $urgente ? $urgentes[array_rand($urgentes)] : $normales[array_rand($normales)],
                'status' => 'pending',
                'created_at' => now()->subDays(rand(0, 29)),
                'updated_at' => now(),
            ]);
        }

        // ---------------------------------------------------------------
        // 5. RECALCULAR REPUTACIÓN (Markov) para todos los usuarios
        // ---------------------------------------------------------------
        $markov = new MarkovReputationService();
        foreach (User::all() as $u) {
            $markov->actualizarEstado($u);
        }

        $this->command->info('DemoSeeder: ' . count($creados) . ' estudiantes, ' . count($pubIds) . ' publicaciones, 36 reportes, reputaciones calculadas.');
    }
}
