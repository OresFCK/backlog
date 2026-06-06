<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('steam_game_achievements_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('steam_id');
            $table->unsignedBigInteger('steam_app_id');
            $table->unsignedInteger('unlocked')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->unsignedTinyInteger('percent')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'steam_app_id']);
            $table->index(['steam_id', 'steam_app_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('steam_game_achievements_cache');
    }
};