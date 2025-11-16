<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categorias_articulos')->insert([
            [
                'Nombre_Categoria' => 'Electrónica',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Nombre_Categoria' => 'Ropa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Nombre_Categoria' => 'Libros',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Nombre_Categoria' => 'Hogar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Nombre_Categoria' => 'Deportes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
