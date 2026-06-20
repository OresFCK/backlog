<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anticipated_games', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Game::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anticipated_games');
    }
};