<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaForoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            'Discusión General',
            'Anuncios y Novedades',
            'Soporte y Ayuda',
            'Sugerencias de la Comunidad',
            'Innovación y Emprendimiento',
            'Compra y Venta',
            'Eventos',
            'Off-Topic',
            'Problemas Solucionados',
            'Consejos y Recomendaciones'
        ];

        $insertData = [];
        foreach ($categorias as $categoria) {
            $insertData[] = [
                'Nombre_Categoria' => $categoria,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('categorias_articulos')->insertOrIgnore($insertData);
    }
}
