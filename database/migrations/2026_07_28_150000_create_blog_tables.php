<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->string('slug')->unique();
            $table->string('excerpt', 320)->nullable();
            $table->longText('body');
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('blog_post_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('value');
            $table->timestamps();
            $table->unique(['blog_post_id', 'user_id']);
        });

        Schema::create('blog_post_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('reporter_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('reason', 500)->nullable();
            $table->string('status', 20)->default('open')->index();
            $table->timestamps();
            $table->unique(['blog_post_id', 'reporter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_reports');
        Schema::dropIfExists('blog_post_votes');
        Schema::dropIfExists('blog_posts');
    }
};
