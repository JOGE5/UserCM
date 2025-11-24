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
            if (!Schema::hasColumn('foros', 'Cod_Categoria')) {
                $table->unsignedBigInteger('Cod_Categoria')->after('Imagen_Foro')->default(1);
                $table->foreign('Cod_Categoria')->references('Cod_Categoria')->on('categorias_articulos')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foros', function (Blueprint $table) {
            if (Schema::hasColumn('foros', 'Cod_Categoria')) {
                $table->dropForeign(['Cod_Categoria']);
                $table->dropColumn('Cod_Categoria');
            }
        });
    }
};
