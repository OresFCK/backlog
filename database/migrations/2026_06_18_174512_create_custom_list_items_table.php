<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_list_items', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('custom_list_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('game_id');
            $table->string('game_title');
            $table->string('game_cover_url')->nullable();
            $table->string('game_slug')->nullable();
            $table->string('steam_app_id')->nullable();

            $table->unsignedInteger('position')->default(1);
            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique([
                'custom_list_id',
                'game_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_list_items');
    }
};