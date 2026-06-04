<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_games', function (Blueprint $table) {
            if (! Schema::hasColumn('custom_games', 'igdb_slug')) {
                $table->string('igdb_slug')->nullable()->index();
            }

            if (! Schema::hasColumn('custom_games', 'igdb_url')) {
                $table->string('igdb_url')->nullable();
            }

            if (! Schema::hasColumn('custom_games', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('custom_games', 'release_date')) {
                $table->date('release_date')->nullable();
            }

            if (! Schema::hasColumn('custom_games', 'developer')) {
                $table->string('developer')->nullable();
            }

            if (! Schema::hasColumn('custom_games', 'header_image_url')) {
                $table->string('header_image_url')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('custom_games', function (Blueprint $table) {
            $table->dropColumn([
                'igdb_slug',
                'igdb_url',
                'description',
                'release_date',
                'developer',
                'header_image_url',
            ]);
        });
    }
};