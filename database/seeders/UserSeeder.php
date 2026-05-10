<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UsuarioCampusMarket;
use App\Models\Universidad;
use App\Models\Carrera;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $unifranz = Universidad::where('Nombre_Universidad', 'Universidad Privada Franz Tamayo')->first()
            ?? Universidad::first();
        $carrera = Carrera::where('Cod_Universidad', $unifranz->Cod_Universidad ?? 1)->first()
            ?? Carrera::first();

        // Administrador del sistema (datos ficticios)
        $admin = User::updateOrCreate(
            ['email' => 'admin@campus.local'],
            [
                'name' => 'Administrador del Sistema',
                'password' => Hash::make('Admin12345'),
                'email_verified_at' => now(),
            ]
        );

        UsuarioCampusMarket::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'Apellidos' => 'del Sistema',
                'Genero' => 'Otro',
                'Telefono' => '70000000',
                'Cod_Rol' => 1, // SuperAdministrador
                'Cod_Carrera' => $carrera->Cod_Carrera ?? 1,
                'Cod_Universidad' => $unifranz->Cod_Universidad ?? 1,
            ]
        );

        // Estudiante de prueba (datos ficticios)
        $estudiante = User::updateOrCreate(
            ['email' => 'estudiante@campus.local'],
            [
                'name' => 'Estudiante Demo',
                'password' => Hash::make('Estudiante123'),
                'email_verified_at' => now(),
            ]
        );

        UsuarioCampusMarket::updateOrCreate(
            ['user_id' => $estudiante->id],
            [
                'Apellidos' => 'Demo',
                'Genero' => 'Otro',
                'Telefono' => '60000000',
                'Cod_Rol' => 3, // Estudiante
                'Cod_Carrera' => $carrera->Cod_Carrera ?? 1,
                'Cod_Universidad' => $unifranz->Cod_Universidad ?? 1,
            ]
        );
    }
}
