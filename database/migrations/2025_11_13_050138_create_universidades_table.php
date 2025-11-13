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
            $table->string('Foto_Universidad')->nullable();
            $table->string('Descripcion_Universidad')->nullable();
            $table->string('Ubicacion_Universidad')->nullable();
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
