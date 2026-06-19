<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada categoría puede pertenecer a una carrera (nullable = categoría global,
     * visible para todas las carreras). Las carreras "tienen" categorías.
     */
    public function up(): void
    {
        Schema::table('categorias_articulos', function (Blueprint $table) {
            $table->unsignedBigInteger('Cod_Carrera')->nullable()->after('Nombre_Categoria');
            $table->foreign('Cod_Carrera')->references('Cod_Carrera')->on('carreras')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('categorias_articulos', function (Blueprint $table) {
            $table->dropForeign(['Cod_Carrera']);
            $table->dropColumn('Cod_Carrera');
        });
    }
};
