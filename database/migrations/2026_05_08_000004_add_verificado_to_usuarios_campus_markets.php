<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios_campus_markets', function (Blueprint $table) {
            $table->boolean('verificado')->default(false)->after('Cod_Universidad');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios_campus_markets', function (Blueprint $table) {
            $table->dropColumn('verificado');
        });
    }
};
