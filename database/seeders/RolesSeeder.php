<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['Cod_Rol' => 1, 'Nombre_Rol' => 'SuperAdministrador', 'Descripcion' => 'Tiene acceso total al sistema.'],
            ['Cod_Rol' => 2, 'Nombre_Rol' => 'Moderador', 'Descripcion' => 'Puede gestionar publicaciones y usuarios.'],
            ['Cod_Rol' => 3, 'Nombre_Rol' => 'Estudiante', 'Descripcion' => 'Rol por defecto para nuevos usuarios.'],
        ]);
    }
}
