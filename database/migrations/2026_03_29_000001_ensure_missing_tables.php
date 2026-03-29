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
        // 1. CHATS
        if (!Schema::hasTable('chats')) {
            Schema::create('chats', function (Blueprint $table) {
                $table->id();
                $table->enum('tipo', ['general', 'privado'])->default('privado');
                $table->string('nombre')->nullable();
                $table->timestamps();
            });
        }

        // 2. MESSAGES
        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('mensaje');
                $table->timestamps();
            });
        }

        // 3. FAVORITOS
        if (!Schema::hasTable('favoritos')) {
            Schema::create('favoritos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('publicacion_id')->constrained('publicaciones')->onDelete('cascade');
                $table->timestamps();
                $table->unique(['user_id', 'publicacion_id']);
            });
        }

        // 4. CHAT_USERS (Pivot)
        if (!Schema::hasTable('chat_users')) {
            Schema::create('chat_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No borramos datos por seguridad en esta migración de rescate
    }
};
