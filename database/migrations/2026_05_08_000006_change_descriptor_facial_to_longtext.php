<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cambia de json a longtext para permitir almacenar el valor cifrado
        // (el cast encrypted:array se encarga de json_encode/decode internamente)
        Schema::table('users', function (Blueprint $table) {
            $table->longText('descriptor_facial')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('descriptor_facial')->nullable()->change();
        });
    }
};
