<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_game_meta', function (Blueprint $table) {
            $table
                ->boolean('show_on_public_profile')
                ->default(false)
                ->after('not_recommended');
        });
    }

    public function down(): void
    {
        Schema::table('user_game_meta', function (Blueprint $table) {
            $table->dropColumn('show_on_public_profile');
        });
    }
};
