<?php

namespace App\Services;

use App\Helpers\GameTitleNormalizer;
use App\Models\Game;
use Illuminate\Support\Collection;

class ReviewGameResolver
{
    public function resolveMany(Collection $reviews): Collection
    {
        if ($reviews->isEmpty()) {
            return collect();
        }

        $steamIds = $reviews
            ->where('source', 'steam')
            ->pluck('source_game_id')
            ->filter()
            ->unique()
            ->values();

        $igdbIds = $reviews
            ->where('source', 'igdb')
            ->pluck('source_game_id')
            ->filter(fn ($id) => ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $localIds = $reviews
            ->pluck('game_id')
            ->filter(fn ($id) => ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $normalizedTitles = $reviews
            ->pluck('game_title')
            ->filter()
            ->map(fn ($title) => GameTitleNormalizer::normalize($title))
            ->filter()
            ->unique()
            ->values();

        $games = Game::query()
            ->where(function ($query) use (
                $steamIds,
                $igdbIds,
                $localIds,
                $normalizedTitles
            ) {
                $query->whereRaw('1 = 0');

                if ($steamIds->isNotEmpty()) {
                    $query->orWhereIn('steam_app_id', $steamIds);
                }

                if ($igdbIds->isNotEmpty()) {
                    $query->orWhereIn('igdb_id', $igdbIds);
                }

                if ($localIds->isNotEmpty()) {
                    $query->orWhereIntegerInRaw('id', $localIds->all());
                }

                if ($normalizedTitles->isNotEmpty()) {
                    $query->orWhereIn('normalized_title', $normalizedTitles);
                }
            })
            ->get();

        $bySteamId = $games
            ->filter(fn (Game $game) => filled($game->steam_app_id))
            ->keyBy(fn (Game $game) => (string) $game->steam_app_id);
        $byIgdbId = $games
            ->filter(fn (Game $game) => filled($game->igdb_id))
            ->keyBy(fn (Game $game) => (string) $game->igdb_id);
        $byTitle = $games
            ->filter(fn (Game $game) => filled($game->normalized_title))
            ->keyBy('normalized_title');
        $byLocalId = $games->keyBy(fn (Game $game) => (string) $game->id);

        return $reviews->mapWithKeys(function ($review) use (
            $bySteamId,
            $byIgdbId,
            $byTitle,
            $byLocalId
        ) {
            $game = null;

            if ($review->source === 'steam' && filled($review->source_game_id)) {
                $game = $bySteamId->get((string) $review->source_game_id);
            }

            if (! $game && $review->source === 'igdb' && filled($review->source_game_id)) {
                $game = $byIgdbId->get((string) $review->source_game_id);
            }

            if (! $game && filled($review->game_title)) {
                $game = $byTitle->get(
                    GameTitleNormalizer::normalize($review->game_title)
                );
            }

            if (! $game && blank($review->game_title)) {
                $game = $byLocalId->get((string) $review->game_id);
            }

            return $game ? [$review->id => $game] : [];
        });
    }
}
