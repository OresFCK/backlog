<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_games', function (Blueprint $table) {
            if (! Schema::hasColumn('custom_games', 'playtime_minutes')) {
                $table->unsignedInteger('playtime_minutes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('custom_games', function (Blueprint $table) {
            if (Schema::hasColumn('custom_games', 'playtime_minutes')) {
                $table->dropColumn('playtime_minutes');
            }
        });
    }
};