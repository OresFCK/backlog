<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_steam_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('steam_app_id');
            $table->string('name')->nullable();
            $table->integer('playtime_forever')->default(0);
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'steam_app_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_steam_games');
    }
};
