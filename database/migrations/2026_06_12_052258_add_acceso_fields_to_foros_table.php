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
            $table->enum('tipo_acceso', ['abierto', 'exclusivo'])->default('abierto')->after('Cod_Categoria');
            $table->unsignedBigInteger('carrera_destino')->nullable()->after('tipo_acceso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foros', function (Blueprint $table) {
            $table->dropColumn(['tipo_acceso', 'carrera_destino']);
        });
    }
};
