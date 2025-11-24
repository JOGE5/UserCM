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
        Schema::create('universidades', function (Blueprint $table) {
            $table->id('Cod_Universidad');
            $table->string('Nombre_Universidad', 120);
            $table->string('Correo_Universidad', 120)->unique();
            $table->string('Telefono_Universidad', 20)->nullable();
            $table->string('Direccion_Universidad', 255)->nullable();
            $table->string('Universisdad_foto_de_portada')->nullable();
            $table->string('Universisdad_foto_de_perfil')->nullable();
            $table->string('Sitio_Web')->nullable();
            $table->text('Descripcion')->nullable();
            $table->time('Hora_apertura')->nullable();
            $table->time('Hora_cierre')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universidades');
    }
};
