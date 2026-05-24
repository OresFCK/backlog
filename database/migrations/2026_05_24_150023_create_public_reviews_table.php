<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('game_id');
            $table->string('title')->nullable();
            $table->text('body');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->boolean('recommended')->default(false);
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->unique([
                'user_id',
                'game_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_reviews');
    }
};