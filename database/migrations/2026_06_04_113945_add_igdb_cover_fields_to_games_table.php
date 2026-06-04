<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            if (! Schema::hasColumn('games', 'igdb_cover_id')) {
                $table->unsignedBigInteger('igdb_cover_id')->nullable()->index();
            }

            if (! Schema::hasColumn('games', 'igdb_cover_image_id')) {
                $table->string('igdb_cover_image_id')->nullable()->index();
            }

            if (! Schema::hasColumn('games', 'igdb_cover_url')) {
                $table->string('igdb_cover_url')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            if (Schema::hasColumn('games', 'igdb_cover_url')) {
                $table->dropColumn('igdb_cover_url');
            }

            if (Schema::hasColumn('games', 'igdb_cover_image_id')) {
                $table->dropColumn('igdb_cover_image_id');
            }

            if (Schema::hasColumn('games', 'igdb_cover_id')) {
                $table->dropColumn('igdb_cover_id');
            }
        });
    }
};