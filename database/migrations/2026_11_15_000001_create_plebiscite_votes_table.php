<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plebiscite_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category_key', 80);
            $table->string('candidate_key', 100);
            $table->timestamps();
            $table->unique(['user_id', 'category_key']);
            $table->index(['category_key', 'candidate_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plebiscite_votes');
    }
};
