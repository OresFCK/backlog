<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_reviews', function (Blueprint $table) {
            $table->json('image_paths')->nullable()->after('screenshot_path');
            $table->string('image_layout', 20)->default('grid')->after('image_paths');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('image_layout', 20)->default('grid')->after('image_paths');
        });
    }

    public function down(): void
    {
        Schema::table('public_reviews', function (Blueprint $table) {
            $table->dropColumn(['image_paths', 'image_layout']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('image_layout');
        });
    }
};
