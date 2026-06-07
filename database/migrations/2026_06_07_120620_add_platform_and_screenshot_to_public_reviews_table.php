<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_reviews', function (Blueprint $table) {
            $table->string('platform')->nullable()->after('rating');
            $table->string('screenshot_path')->nullable()->after('platform');
        });
    }

    public function down(): void
    {
        Schema::table('public_reviews', function (Blueprint $table) {
            $table->dropColumn([
                'platform',
                'screenshot_path',
            ]);
        });
    }
};