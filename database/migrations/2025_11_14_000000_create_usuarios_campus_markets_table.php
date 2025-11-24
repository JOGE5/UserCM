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
            $table-> id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('Apellidos')->nullable();
            $table->enum('Genero', ['Masculino', 'Femenino', 'Otro'])->nullable();
            $table->enum('Estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->string('Telefono')->nullable();
            $table->string('Foto_de_portada')->nullable();
            $table->string('Foto_de_perfil')->nullable();
            $table->unsignedBigInteger('Cod_Rol')->default(3);
            $table->unsignedBigInteger('Cod_Carrera');
            $table->unsignedBigInteger('Cod_Universidad');

            // timestamps solo UNA VEZ
            $table->timestamps();

            // foreign keys
            $table->foreign('Cod_Rol')->references('Cod_Rol')->on('roles');
            $table->foreign('Cod_Carrera')->references('Cod_Carrera')->on('carreras');
            $table->foreign('Cod_Universidad')->references('Cod_Universidad')->on('universidades');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios_campus_markets');
    }
};
