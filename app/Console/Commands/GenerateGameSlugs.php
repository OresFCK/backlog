<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateGameSlugs extends Command
{
    protected $signature = 'games:generate-slugs';

    protected $description = 'Generate missing SEO slugs for games';

    public function handle(): int
    {
        Game::query()
            ->whereNull('slug')
            ->orWhere('slug', '')
            ->orderBy('id')
            ->chunkById(100, function ($games) {
                foreach ($games as $game) {
                    $baseSlug = Str::slug($game->title);
                    $slug = $baseSlug;
                    $counter = 2;

                    while (
                        Game::query()
                            ->where('slug', $slug)
                            ->whereKeyNot($game->id)
                            ->exists()
                    ) {
                        $slug = "{$baseSlug}-{$counter}";
                        $counter++;
                    }

                    $game->update([
                        'slug' => $slug,
                    ]);
                }
            });

        $this->info('Game slugs generated.');

        return self::SUCCESS;
    }
}