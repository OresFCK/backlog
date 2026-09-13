<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nintendo_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('session_token');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::table('custom_games', function (Blueprint $table) {
            $table->string('nintendo_title_id')->nullable()->index();
            $table->timestamp('first_played_at')->nullable();
            $table->timestamp('last_played_at')->nullable();
            $table->unique(['user_id', 'nintendo_title_id']);
        });
    }

    public function down(): void
    {
        Schema::table('custom_games', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'nintendo_title_id']);
            $table->dropColumn(['nintendo_title_id', 'first_played_at', 'last_played_at']);
        });

        Schema::dropIfExists('nintendo_accounts');
    }
};
