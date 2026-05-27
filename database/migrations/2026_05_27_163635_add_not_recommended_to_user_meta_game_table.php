<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_game_meta', function (Blueprint $table) {
            $table->boolean('not_recommended')
                ->default(false)
                ->after('recommended');
        });

        Schema::table('public_reviews', function (Blueprint $table) {
            $table->boolean('not_recommended')
                ->default(false)
                ->after('recommended');
        });
    }

    public function down(): void
    {
        Schema::table('user_game_meta', function (Blueprint $table) {
            $table->dropColumn('not_recommended');
        });

        Schema::table('public_reviews', function (Blueprint $table) {
            $table->dropColumn('not_recommended');
        });
    }
};