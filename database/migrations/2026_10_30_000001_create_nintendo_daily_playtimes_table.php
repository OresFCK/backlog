<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nintendo_daily_playtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_game_id')->constrained()->cascadeOnDelete();
            $table->date('played_on');
            $table->unsignedInteger('playtime_minutes')->default(0);
            $table->timestamps();
            $table->unique(['custom_game_id', 'played_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nintendo_daily_playtimes');
    }
};
