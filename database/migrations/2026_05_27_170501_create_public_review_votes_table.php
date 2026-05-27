<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_review_votes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('public_review_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->tinyInteger('value');

            $table->timestamps();

            $table->unique([
                'public_review_id',
                'user_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'public_review_votes'
        );
    }
};