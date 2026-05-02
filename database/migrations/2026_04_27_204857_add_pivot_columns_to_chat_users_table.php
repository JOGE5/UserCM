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
        Schema::table('chat_users', function (Blueprint $table) {
            $table->boolean('is_muted')->default(false)->after('user_id');
            $table->boolean('is_hidden')->default(false)->after('is_muted');
            $table->timestamp('last_read_at')->nullable()->after('is_hidden');
        });
    }

    public function down(): void
    {
        Schema::table('chat_users', function (Blueprint $table) {
            $table->dropColumn(['is_muted', 'is_hidden', 'last_read_at']);
        });
    }
};
