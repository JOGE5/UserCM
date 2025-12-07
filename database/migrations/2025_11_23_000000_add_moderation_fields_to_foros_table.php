<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('foros', function (Blueprint $table) {
            $table->string('moderation_status')->default('approved')->after('Estado_Foro');
            $table->json('moderation_scores')->nullable()->after('moderation_status');
            $table->timestamp('moderation_checked_at')->nullable()->after('moderation_scores');
            $table->string('moderation_reason')->nullable()->after('moderation_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('foros', function (Blueprint $table) {
            $table->dropColumn(['moderation_status','moderation_scores','moderation_checked_at','moderation_reason']);
        });
    }
};
