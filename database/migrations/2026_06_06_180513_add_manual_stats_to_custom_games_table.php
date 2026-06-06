<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_games', function (Blueprint $table) {
            $table->unsignedInteger('achievements_unlocked')->nullable()->after('playtime_minutes');
            $table->unsignedInteger('achievements_total')->nullable()->after('achievements_unlocked');
        });
    }

    public function down(): void
    {
        Schema::table('custom_games', function (Blueprint $table) {
            $table->dropColumn([
                'playtime_minutes',
                'achievements_unlocked',
                'achievements_total',
            ]);
        });
    }
};