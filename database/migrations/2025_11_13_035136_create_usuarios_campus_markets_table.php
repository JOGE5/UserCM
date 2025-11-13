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
        Schema::create('usuarios_campus_markets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('Apellidos')->nullable();
            $table->enum('Genero', ['Masculino', 'Femenino', 'Otro'])->nullable();
            $table->enum('Estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->string('Telefono')->nullable();
            $table->string('Foto_de_portada')->nullable();
            $table->string('Foto_de_perfil')->nullable();
            $table->foreignId('Cod_Rol')->nullable()->constrained('roles', 'Cod_Rol')->onDelete('set null');
            $table->foreignId('Cod_Carrera')->nullable()->constrained('carreras', 'Cod_Carrera')->onDelete('set null');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios_campus_market');
    }
};
