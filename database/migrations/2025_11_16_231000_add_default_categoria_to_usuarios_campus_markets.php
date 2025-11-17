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
        Schema::table('usuarios_campus_markets', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios_campus_markets', 'Cod_Categoria_Default')) {
                $table->unsignedBigInteger('Cod_Categoria_Default')->nullable()->after('Cod_Carrera');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios_campus_markets', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios_campus_markets', 'Cod_Categoria_Default')) {
                $table->dropColumn('Cod_Categoria_Default');
            }
        });
    }
};
