<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('steam_app_id')
                ->nullable()
                ->unique();

            $table->string('title');

            $table->string('slug')->unique();

            $table->string('cover_url')->nullable();
            $table->string('header_image_url')->nullable();

            $table->date('release_date')->nullable();

            $table->unsignedTinyInteger('metacritic_score')->nullable();

            $table->unsignedTinyInteger('steam_rating_percent')->nullable();

            $table->unsignedInteger('average_playtime_minutes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};