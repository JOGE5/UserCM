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
        Schema::table('publicaciones', function (Blueprint $table) {
            // Cambiar columna Estado_Publicacion por estado (enum)
            // Estados: 'activa', 'borrador', 'eliminada'
            $table->string('estado')->default('activa')->after('Estado_Publicacion');
            
            // Soft delete para eliminación lógica
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->dropColumn('estado');
            $table->dropSoftDeletes();
        });
    }
};
