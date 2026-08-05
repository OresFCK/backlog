<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_steam_games', function (Blueprint $table) {
            $table->timestamp('last_played_at')
                ->nullable()
                ->after('playtime_forever');
        });
    }

    public function down(): void
    {
        Schema::table('user_steam_games', function (Blueprint $table) {
            $table->dropColumn('last_played_at');
        });
    }
};
