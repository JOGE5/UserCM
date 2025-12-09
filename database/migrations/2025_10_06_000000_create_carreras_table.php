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
        Schema::create('carreras', function (Blueprint $table) {
            $table->id('Cod_Carrera');
            $table->string('Nombre_Carrera', 120);
            $table->unsignedBigInteger('Cod_Universidad');
            $table->string('Foto_Carrera')->nullable();
            $table->string('Descripcion_Carrera')->nullable();
            $table->string('Duracion_Carrera')->nullable();
            $table->timestamps();

            $table->foreign('Cod_Universidad')->references('Cod_Universidad')->on('universidades');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carreras');
    }
};
