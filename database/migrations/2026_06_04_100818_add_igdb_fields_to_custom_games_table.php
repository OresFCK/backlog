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
        Schema::table('custom_games', function (Blueprint $table) {
            if (! Schema::hasColumn('custom_games', 'igdb_id')) {
                $table->unsignedBigInteger('igdb_id')->nullable()->index();
            }

            if (! Schema::hasColumn('custom_games', 'normalized_title')) {
                $table->string('normalized_title')->nullable()->index();
            }

            if (! Schema::hasColumn('custom_games', 'source')) {
                $table->string('source')->default('manual')->index();
            }
        });

        DB::table('custom_games')
            ->whereNull('normalized_title')
            ->orderBy('id')
            ->get(['id', 'title'])
            ->each(function ($game) {
                DB::table('custom_games')
                    ->where('id', $game->id)
                    ->update([
                        'normalized_title' => GameTitleNormalizer::normalize($game->title),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('custom_games', function (Blueprint $table) {
            if (Schema::hasColumn('custom_games', 'source')) {
                $table->dropColumn('source');
            }

            if (Schema::hasColumn('custom_games', 'normalized_title')) {
                $table->dropColumn('normalized_title');
            }

            if (Schema::hasColumn('custom_games', 'igdb_id')) {
                $table->dropColumn('igdb_id');
            }
        });
    }
};