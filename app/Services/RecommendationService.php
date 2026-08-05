<?php

namespace App\Services;

use App\Models\Game;
use App\Models\PublicReview;
use App\Models\UserConnection;
use App\Models\UserGameMeta;
use App\Models\UserSteamGame;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RecommendationService
{
    private ?Collection $recommendationsCache = null;

    private ?Collection $ownedGameIdsCache = null;

    private ?Collection $friendIdsCache = null;

    private ?array $tasteProfileCache = null;

    private ?Collection $excludedGameIdsCache = null;

    private ?Collection $ownedSteamGamesCache = null;

    public function __construct(
        private ReviewGameResolver $reviewGameResolver
    ) {}

    public function backlogRecommendations(): array
    {
        return $this->buildRecommendations()
            ->whereIn('library_game_id', $this->ownedGameIds())
            ->whereNotIn('library_game_id', $this->excludedGameIds())
            ->filter(fn ($item) => filled($item['game']['public_url'] ?? null))
            ->sortByDesc('score')
            ->take(10)
            ->values()
            ->toArray();
    }

    public function steamRecommendations(): array
    {
        return $this->buildRecommendations()
            ->whereNotIn('library_game_id', $this->ownedGameIds())
            ->filter(fn ($item) => filled($item['game']['public_url'] ?? null))
            ->sortByDesc('score')
            ->take(10)
            ->values()
            ->toArray();
    }

    public function friendsRanking(): array
    {
        return $this->buildRecommendations()
            ->where('friend_recommendations', '>', 0)
            ->filter(fn ($item) => filled($item['game']['public_url'] ?? null))
            ->sortByDesc('score')
            ->take(10)
            ->values()
            ->toArray();
    }

    public function globalRanking(): array
    {
        return $this->buildRecommendations()
            ->filter(fn ($item) => filled($item['game']['public_url'] ?? null))
            ->sortByDesc('score')
            ->take(10)
            ->values()
            ->toArray();
    }

    private function buildRecommendations(): Collection
    {
        if ($this->recommendationsCache !== null) {
            return $this->recommendationsCache;
        }

        $userId = Auth::id();
        $friendIds = $this->friendIds();

        $reviews = PublicReview::query()
            ->with('votes:id,public_review_id,value')
            ->where('user_id', '!=', $userId)
            ->where(function ($query) {
                $query
                    ->where('recommended', true)
                    ->orWhere('not_recommended', true);
            })
            ->get([
                'id',
                'user_id',
                'game_id',
                'source',
                'source_game_id',
                'game_title',
                'rating',
                'recommended',
                'not_recommended',
            ])
            ->values();

        $gamesByReview = $this->reviewGameResolver->resolveMany($reviews);

        $reviews = $reviews
            ->filter(fn ($review) => $gamesByReview->has($review->id))
            ->each(fn ($review) => $review->setRelation(
                'resolvedGame',
                $gamesByReview->get($review->id)
            ));

        return $this->recommendationsCache = $reviews
            ->groupBy(fn ($review) => (string) $review->resolvedGame->id)
            ->map(function (
                Collection $reviews,
                string $gameId
            ) use ($friendIds) {
                $review = $reviews->first();
                $game = $review->resolvedGame;
                $steamAppId = $this->steamAppId($game, $reviews);

                $friendRecommendations = $reviews
                    ->whereIn('user_id', $friendIds)
                    ->where('recommended', true)
                    ->count();

                $globalRecommendations = $reviews
                    ->where('recommended', true)
                    ->count();

                $negativeRecommendations = $reviews
                    ->where('not_recommended', true)
                    ->count();

                $averageRating = $this->averageRating($reviews);

                $rawVotesScore = $reviews->sum(
                    fn ($review) => $review->votes->sum('value')
                );

                $behaviorScore = $this->behaviorScore($game);
                $explanation = $this->explanation($game, $steamAppId);

                $score = $this->score(
                    $behaviorScore,
                    $friendRecommendations,
                    $globalRecommendations,
                    $negativeRecommendations,
                    $averageRating,
                    $rawVotesScore
                );

                return [
                    'game_id' => $gameId,
                    'library_game_id' => (string) (
                        $steamAppId
                        ?? $game->igdb_id
                        ?? $game->id
                    ),
                    'score' => round($score, 2),
                    'friend_recommendations' => $friendRecommendations,
                    'global_recommendations' => $globalRecommendations,
                    'not_recommended_count' => $negativeRecommendations,
                    'average_rating' => $averageRating,
                    'votes_score' => $rawVotesScore,
                    'behavior_score' => round($behaviorScore, 2),
                    'signals' => $explanation['signals'],

                    'game' => [
                        'id' => $gameId,
                        'steam_app_id' => $steamAppId,
                        'title' => $game->title,
                        'average_playtime_minutes' => $game->average_playtime_minutes,
                        'genres' => $game->genres ?? [],
                        'header_image_url' => filled($steamAppId)
                            ? "https://cdn.cloudflare.steamstatic.com/steam/apps/{$steamAppId}/library_hero.jpg"
                            : ($game->header_image_url
                                ?: $game->cover_url
                                ?: $game->igdb_cover_url),
                        'cover_image_url' => filled($steamAppId)
                            ? "https://cdn.cloudflare.steamstatic.com/steam/apps/{$steamAppId}/library_600x900.jpg"
                            : ($game->cover_url ?: $game->igdb_cover_url),
                        'image_fallback_url' => filled($steamAppId)
                            ? "https://cdn.cloudflare.steamstatic.com/steam/apps/{$steamAppId}/header.jpg"
                            : ($game->cover_url ?: $game->igdb_cover_url),
                        'slug' => $game?->slug,
                        'public_url' => $game?->slug
                            ? route('games.public.show', $game)
                            : null,
                    ],

                    'reason' => $explanation['reason'] ?: $this->reasonText(
                        $behaviorScore,
                        $friendRecommendations,
                        $globalRecommendations,
                        $averageRating
                    ),
                ];
            })
            ->sortByDesc('score')
            ->values();
    }

    private function steamAppId(Game $game, Collection $reviews): ?string
    {
        if (filled($game->steam_app_id)) {
            return (string) $game->steam_app_id;
        }

        $sourceId = $reviews
            ->first(fn ($review) => in_array(
                $review->source,
                ['steam', 'merged'],
                true
            ) && ctype_digit((string) $review->source_game_id))
            ?->source_game_id;

        return filled($sourceId) ? (string) $sourceId : null;
    }

    private function explanation(Game $game, ?string $steamAppId): array
    {
        $profile = $this->tasteProfile();
        $gameGenres = collect($game->genres ?? [])->map(fn ($id) => (string) $id);
        $similarLovedCount = collect($profile['loved_games'] ?? [])
            ->filter(fn (array $lovedGame) => $gameGenres
                ->intersect($lovedGame['genres'])
                ->isNotEmpty())
            ->count();
        $ownedGame = $steamAppId
            ? $this->ownedSteamGames()->get($steamAppId)
            : null;
        $hours = $game->average_playtime_minutes
            ? max(1, (int) round($game->average_playtime_minutes / 60))
            : null;

        $signals = collect([
            $similarLovedCount > 0
                ? "Similar to {$similarLovedCount} ".($similarLovedCount === 1 ? 'game' : 'games').' you loved'
                : null,
            $hours ? "Around {$hours} hours" : null,
            $ownedGame ? 'In your Steam library' : 'New Steam discovery',
        ])->filter()->take(3)->values()->all();

        if ($similarLovedCount > 0) {
            $reason = "You rated {$similarLovedCount} similar ".($similarLovedCount === 1 ? 'game' : 'games').' highly.';
        } elseif ($ownedGame && (int) $ownedGame->playtime_forever === 0) {
            $reason = 'A strong match that is still untouched in your Steam library.';
        } elseif ($hours) {
            $reason = "A strong match for your play history. Around {$hours} hours to finish.";
        } else {
            $reason = null;
        }

        return compact('reason', 'signals');
    }

    private function ownedSteamGames(): Collection
    {
        return $this->ownedSteamGamesCache
            ??= UserSteamGame::query()
                ->where('user_id', Auth::id())
                ->get(['steam_app_id', 'playtime_forever', 'last_played_at'])
                ->keyBy(fn ($game) => (string) $game->steam_app_id);
    }

    private function averageRating(Collection $reviews): ?float
    {
        $ratings = $reviews->whereNotNull('rating');

        if ($ratings->isEmpty()) {
            return null;
        }

        return round((float) $ratings->avg('rating'), 1);
    }

    private function score(
        float $behaviorScore,
        int $friendRecommendations,
        int $globalRecommendations,
        int $negativeRecommendations,
        ?float $averageRating,
        int|float $rawVotesScore
    ): float {
        $ratingScore = $averageRating !== null
            ? $averageRating * 2
            : 10;

        $votesScore = $rawVotesScore > 0
            ? log(1 + $rawVotesScore) * 3
            : 0;

        return
            $behaviorScore +
            ($friendRecommendations * 10) +
            (log(1 + $globalRecommendations) * 10) +
            $ratingScore +
            $votesScore -
            ($negativeRecommendations * 8);
    }

    private function ownedGameIds(): Collection
    {
        if ($this->ownedGameIdsCache !== null) {
            return $this->ownedGameIdsCache;
        }

        $steamIds = UserSteamGame::query()
            ->where('user_id', Auth::id())
            ->pluck('steam_app_id');

        $metaIds = UserGameMeta::query()
            ->where('user_id', Auth::id())
            ->pluck('game_id');

        return $this->ownedGameIdsCache = $steamIds
            ->merge($metaIds)
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();
    }

    private function excludedGameIds(): Collection
    {
        return $this->excludedGameIdsCache
            ??= UserGameMeta::query()
                ->where('user_id', Auth::id())
                ->whereIn('status', ['Finished', 'Dropped'])
                ->pluck('game_id')
                ->map(fn ($id) => (string) $id);
    }

    /**
     * Build genre preferences from observed playtime first, then refine them
     * with explicit ratings and completion signals when those exist.
     */
    private function tasteProfile(): array
    {
        if ($this->tasteProfileCache !== null) {
            return $this->tasteProfileCache;
        }

        $userId = Auth::id();
        $steamGames = UserSteamGame::query()
            ->where('user_id', $userId)
            ->where('playtime_forever', '>', 0)
            ->get(['steam_app_id', 'playtime_forever', 'last_played_at']);

        $metas = UserGameMeta::query()
            ->where('user_id', $userId)
            ->get(['game_id', 'rating', 'recommended', 'not_recommended', 'status'])
            ->keyBy(fn ($meta) => (string) $meta->game_id);

        $games = Game::query()
            ->whereIn('steam_app_id', $steamGames->pluck('steam_app_id'))
            ->get(['steam_app_id', 'genres'])
            ->keyBy(fn ($game) => (string) $game->steam_app_id);

        $weights = [];
        $lovedGames = [];

        foreach ($steamGames as $steamGame) {
            $game = $games->get((string) $steamGame->steam_app_id);
            if (! $game) {
                continue;
            }

            $meta = $metas->get((string) $steamGame->steam_app_id);
            $hours = max(0, (int) $steamGame->playtime_forever) / 60;
            $weight = min(4.5, log(1 + $hours, 2));

            if ($steamGame->last_played_at) {
                $daysSincePlayed = $steamGame->last_played_at->diffInDays(now());
                $recencyMultiplier = match (true) {
                    $daysSincePlayed <= 14 => 1.6,
                    $daysSincePlayed <= 60 => 1.35,
                    $daysSincePlayed <= 180 => 1.15,
                    $daysSincePlayed > 730 => 0.7,
                    default => 1.0,
                };
                $weight *= $recencyMultiplier;
            }

            $sentiment = $meta?->rating !== null
                ? max(-1, min(1.5, ((int) $meta->rating - 5) / 3))
                : 1.0;

            if ($meta?->recommended) {
                $sentiment = max(1.2, $sentiment);
            } elseif ($meta?->not_recommended) {
                $sentiment = min(-0.8, $sentiment);
            }

            $status = strtolower((string) $meta?->status);
            if ($status === 'finished' && $sentiment > 0) {
                $sentiment *= 1.15;
            } elseif ($status === 'dropped') {
                $sentiment = -max(0.7, abs($sentiment));
            }

            $weight *= $sentiment;

            if (($meta?->rating ?? 0) >= 8 || $meta?->recommended) {
                $lovedGames[] = [
                    'game_id' => (string) $steamGame->steam_app_id,
                    'genres' => collect($game->genres ?? [])
                        ->map(fn ($genreId) => (string) $genreId)
                        ->all(),
                ];
            }

            foreach ($game->genres ?? [] as $genreId) {
                $key = (string) $genreId;
                $weights[$key] = ($weights[$key] ?? 0) + $weight;
            }
        }

        $maxPositiveWeight = max(0.0, ...array_values($weights ?: [0]));

        return $this->tasteProfileCache = [
            'genres' => $weights,
            'max_positive_weight' => $maxPositiveWeight,
            'observed_games' => $steamGames->count(),
            'loved_games' => $lovedGames,
        ];
    }

    private function behaviorScore(Game $game): float
    {
        $profile = $this->tasteProfile();
        $genres = collect($game->genres ?? [])
            ->map(fn ($genreId) => (string) $genreId);

        if ($genres->isEmpty() || $profile['max_positive_weight'] <= 0) {
            return 0;
        }

        $matchingWeights = $genres
            ->map(fn ($genreId) => $profile['genres'][$genreId] ?? 0.0);
        $positive = max(0.0, (float) $matchingWeights->avg());
        $negative = abs(min(0.0, (float) $matchingWeights->min()));

        return max(
            -45,
            min(80, (($positive - $negative) / $profile['max_positive_weight']) * 80)
        );
    }

    private function friendIds(): Collection
    {
        if ($this->friendIdsCache !== null) {
            return $this->friendIdsCache;
        }

        $userId = Auth::id();

        return $this->friendIdsCache = UserConnection::query()
            ->where(function ($query) use ($userId) {
                $query
                    ->where(function ($query) use ($userId) {
                        $query
                            ->where('type', 'friend')
                            ->where('status', 'accepted')
                            ->where('sender_id', $userId);
                    })
                    ->orWhere(function ($query) use ($userId) {
                        $query
                            ->where('type', 'friend')
                            ->where('status', 'accepted')
                            ->where('receiver_id', $userId);
                    })
                    ->orWhere(function ($query) use ($userId) {
                        $query
                            ->where('type', 'follow')
                            ->where('sender_id', $userId);
                    });
            })
            ->get(['sender_id', 'receiver_id'])
            ->flatMap(fn ($connection) => [
                $connection->sender_id,
                $connection->receiver_id,
            ])
            ->reject(fn ($id) => (int) $id === (int) $userId)
            ->unique()
            ->values();
    }

    private function reasonText(
        float $behaviorScore,
        int $friendRecommendations,
        int $globalRecommendations,
        ?float $averageRating
    ): string {
        if ($behaviorScore >= 45) {
            return 'Matches the kinds of games you play and rate highly.';
        }

        if ($behaviorScore >= 20) {
            return 'Similar to games you spend the most time playing.';
        }

        if ($friendRecommendations >= 3) {
            return 'Your friends highly recommend this game.';
        }

        if ($averageRating !== null && $averageRating >= 8) {
            return 'Players consistently rate this game very highly.';
        }

        if ($globalRecommendations >= 10) {
            return 'One of the most recommended games right now.';
        }

        return 'Trending in the community.';
    }
}
