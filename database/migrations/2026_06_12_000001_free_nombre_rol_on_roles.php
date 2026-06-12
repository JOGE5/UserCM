<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El campo roles.Nombre_Rol se creó como enum (CHECK constraint), lo que
     * impedía crear roles personalizados desde el panel. Lo liberamos a texto libre.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE roles DROP CONSTRAINT IF EXISTS "roles_Nombre_Rol_check"');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE roles ADD CONSTRAINT "roles_Nombre_Rol_check" CHECK ("Nombre_Rol" IN (\'SuperAdministrador\', \'Moderador\', \'Estudiante\'))');
    }
};
