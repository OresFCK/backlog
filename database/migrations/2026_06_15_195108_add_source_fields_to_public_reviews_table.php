<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('public_reviews', 'source')) {
                $table->string('source')->nullable()->after('game_id');
            }

            if (! Schema::hasColumn('public_reviews', 'source_game_id')) {
                $table->string('source_game_id')->nullable()->after('source');
            }

            if (! Schema::hasColumn('public_reviews', 'game_title')) {
                $table->string('game_title')->nullable()->after('source_game_id');
            }

            if (! Schema::hasColumn('public_reviews', 'platform')) {
                $table->string('platform')->nullable()->after('rating');
            }

            if (! Schema::hasColumn('public_reviews', 'screenshot_path')) {
                $table->string('screenshot_path')->nullable()->after('platform');
            }

            if (! Schema::hasColumn('public_reviews', 'not_recommended')) {
                $table->boolean('not_recommended')->default(false)->after('recommended');
            }

            if (! Schema::hasColumn('public_reviews', 'is_featured_on_profile')) {
                $table->boolean('is_featured_on_profile')->default(false)->after('not_recommended');
            }

            if (! Schema::hasColumn('public_reviews', 'time_to_beat_minutes')) {
                $table->unsignedInteger('time_to_beat_minutes')->nullable()->after('is_public');
            }
        });
    }

    public function down(): void
    {
        Schema::table('public_reviews', function (Blueprint $table) {
            $table->dropColumn([
                'source',
                'source_game_id',
                'game_title',
                'platform',
                'screenshot_path',
                'not_recommended',
                'is_featured_on_profile',
                'time_to_beat_minutes',
            ]);
        });
    }
};