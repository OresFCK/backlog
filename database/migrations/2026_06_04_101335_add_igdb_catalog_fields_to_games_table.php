<?php

use App\Helpers\GameTitleNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            if (! Schema::hasColumn('games', 'igdb_id')) {
                $table->unsignedBigInteger('igdb_id')->nullable()->unique()->after('steam_app_id');
            }

            if (! Schema::hasColumn('games', 'normalized_title')) {
                $table->string('normalized_title')->nullable()->index()->after('title');
            }

            if (! Schema::hasColumn('games', 'slug')) {
                $table->string('slug')->nullable()->index()->after('normalized_title');
            }

            if (! Schema::hasColumn('games', 'summary')) {
                $table->text('summary')->nullable()->after('header_image_url');
            }

            if (! Schema::hasColumn('games', 'source')) {
                $table->string('source')->default('steam')->index()->after('summary');
            }
        });

        DB::table('games')
            ->whereNull('normalized_title')
            ->orderBy('id')
            ->get(['id', 'title'])
            ->each(function ($game) {
                DB::table('games')
                    ->where('id', $game->id)
                    ->update([
                        'normalized_title' => GameTitleNormalizer::normalize($game->title),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            if (Schema::hasColumn('games', 'igdb_id')) {
                $table->dropUnique(['igdb_id']);
                $table->dropColumn('igdb_id');
            }

            if (Schema::hasColumn('games', 'normalized_title')) {
                $table->dropColumn('normalized_title');
            }


            if (Schema::hasColumn('games', 'summary')) {
                $table->dropColumn('summary');
            }

            if (Schema::hasColumn('games', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};