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
            $table->unsignedInteger('experiencia')->default(0)->after('verificado');
            $table->unsignedInteger('nivel')->default(1)->after('experiencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios_campus_markets', function (Blueprint $table) {
            $table->dropColumn(['experiencia', 'nivel']);
        });
    }
};
