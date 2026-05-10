<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_games', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('game_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('platform_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('status', [
                'backlog',
                'playing',
                'finished',
                'dropped',
                'wishlist',
            ])->default('backlog');

            $table->enum('priority', [
                'low',
                'medium',
                'high',
            ])->nullable();

            $table->enum('source', [
                'steam_import',
                'manual',
                'wishlist',
            ])->nullable();

            $table->unsignedInteger('playtime_minutes')->nullable();

            $table->unsignedTinyInteger('personal_rating')->nullable();

            $table->text('notes')->nullable();

            $table->timestamp('added_to_library_at')->nullable();

            $table->timestamp('started_at')->nullable();

            $table->timestamp('finished_at')->nullable();

            $table->timestamp('last_played_at')->nullable();

            $table->timestamps();

            $table->unique([
                'user_id',
                'game_id',
                'platform_id',
            ]);

            $table->index([
                'user_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_games');
    }
};