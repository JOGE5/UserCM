<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaForosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categorias_foros')->insert([
            ['Nombre_Categoria' => 'Académico',           'created_at' => now(), 'updated_at' => now()],
            ['Nombre_Categoria' => 'Proyectos',           'created_at' => now(), 'updated_at' => now()],
            ['Nombre_Categoria' => 'Dudas y Consultas',   'created_at' => now(), 'updated_at' => now()],
            ['Nombre_Categoria' => 'Noticias',            'created_at' => now(), 'updated_at' => now()],
            ['Nombre_Categoria' => 'Eventos',             'created_at' => now(), 'updated_at' => now()],
            ['Nombre_Categoria' => 'Oferta Laboral',      'created_at' => now(), 'updated_at' => now()],
            ['Nombre_Categoria' => 'Recursos Educativos', 'created_at' => now(), 'updated_at' => now()],
            ['Nombre_Categoria' => 'General',             'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
