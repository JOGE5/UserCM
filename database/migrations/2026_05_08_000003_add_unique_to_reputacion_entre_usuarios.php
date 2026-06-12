<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar duplicados previos (queda el más reciente por par calificador-calificado)
        DB::statement('
            DELETE FROM reputacion_entre_usuarios
            WHERE "ID_Reputacion" NOT IN (
                SELECT max_id FROM (
                    SELECT MAX("ID_Reputacion") as max_id
                    FROM reputacion_entre_usuarios
                    GROUP BY "ID_Usuario_Calificador", "ID_Usuario_Calificado"
                ) as keepers
            )
        ');

        Schema::table('reputacion_entre_usuarios', function (Blueprint $table) {
            $table->unique(
                ['ID_Usuario_Calificador', 'ID_Usuario_Calificado'],
                'rep_calificador_calificado_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('reputacion_entre_usuarios', function (Blueprint $table) {
            $table->dropUnique('rep_calificador_calificado_unique');
        });
    }
};
