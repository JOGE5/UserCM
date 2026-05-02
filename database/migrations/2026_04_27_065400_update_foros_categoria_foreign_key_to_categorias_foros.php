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
        Schema::table('foros', function (Blueprint $table) {
            // Quitar FK viejo que apuntaba a categorias_articulos
            if (Schema::hasColumn('foros', 'Cod_Categoria')) {
                $table->dropForeign(['Cod_Categoria']);
                // Agregar FK nuevo apuntando a categorias_foros
                $table->foreign('Cod_Categoria')
                      ->references('Cod_Categoria')
                      ->on('categorias_foros')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('foros', function (Blueprint $table) {
            if (Schema::hasColumn('foros', 'Cod_Categoria')) {
                $table->dropForeign(['Cod_Categoria']);
                $table->foreign('Cod_Categoria')
                      ->references('Cod_Categoria')
                      ->on('categorias_articulos')
                      ->onDelete('cascade');
            }
        });
    }
};
