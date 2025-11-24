<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id('Cod_Rol');
            $table->enum('Nombre_Rol', ['SuperAdministrador', 'Moderador', 'Estudiante'])->default('Estudiante');
            $table->string('Descripcion')->nullable();
            $table->string('Foto_Rol')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
