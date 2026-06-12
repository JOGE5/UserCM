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

        // Administrador del sistema
        $admin = User::updateOrCreate(
            ['email' => 'agustinapaza1817@gmail.com'],
            [
                'name' => 'Agustin Apaza',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        UsuarioCampusMarket::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'Apellidos' => 'Cruz Mamani',
                'Genero' => 'Masculino',
                'Telefono' => '70000000',
                'Cod_Rol' => 1, // SuperAdministrador
                'Cod_Carrera' => $carrera->Cod_Carrera ?? 1,
                'Cod_Universidad' => $unifranz->Cod_Universidad ?? 1,
                'Estado' => 'Activo',
            ]
        );
    }
}
