<?php

namespace App\Services;

use App\Models\PublicReview;
use App\Models\UserConnection;
use App\Models\UserGameMeta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RecommendationService
{
    private ?Collection $recommendationsCache = null;
    private ?Collection $ownedGameIdsCache = null;
    private ?Collection $friendIdsCache = null;

    public function backlogRecommendations(): array
    {
        return $this->buildRecommendations()
            ->whereIn('game_id', $this->ownedGameIds())
            ->sortByDesc('score')
            ->take(10)
            ->values()
            ->toArray();
    }

    public function steamRecommendations(): array
    {
        return $this->buildRecommendations()
            ->whereNotIn('game_id', $this->ownedGameIds())
            ->sortByDesc('score')
            ->take(10)
            ->values()
            ->toArray();
    }

    public function friendsRanking(): array
    {
        return $this->buildRecommendations()
            ->where('friend_recommendations', '>', 0)
            ->sortByDesc('score')
            ->take(10)
            ->values()
            ->toArray();
    }

    public function globalRanking(): array
    {
        return $this->buildRecommendations()
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

        return $this->recommendationsCache = PublicReview::query()
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
                'game_title',
                'rating',
                'recommended',
                'not_recommended',
            ])
            ->groupBy('game_id')
            ->map(function (Collection $reviews, string $gameId) use ($friendIds) {
                $review = $reviews->first();

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

                $score = $this->score(
                    $friendRecommendations,
                    $globalRecommendations,
                    $negativeRecommendations,
                    $averageRating,
                    $rawVotesScore
                );

                return [
                    'game_id' => $gameId,
                    'score' => round($score, 2),
                    'friend_recommendations' => $friendRecommendations,
                    'global_recommendations' => $globalRecommendations,
                    'not_recommended_count' => $negativeRecommendations,
                    'average_rating' => $averageRating,
                    'votes_score' => $rawVotesScore,

                    'game' => [
                        'id' => $gameId,
                        'title' => $review->game_title ?? "Game {$gameId}",
                        'header_image_url' => $this->steamHeaderUrl($gameId),
                    ],

                    'reason' => $this->reasonText(
                        $friendRecommendations,
                        $globalRecommendations,
                        $averageRating
                    ),
                ];
            })
            ->sortByDesc('score')
            ->values();
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
        int $friendRecommendations,
        int $globalRecommendations,
        int $negativeRecommendations,
        ?float $averageRating,
        int|float $rawVotesScore
    ): float {
        $ratingScore = $averageRating !== null
            ? $averageRating * 6
            : 30;

        $votesScore = $rawVotesScore > 0
            ? log(1 + $rawVotesScore) * 8
            : 0;

        return
            ($friendRecommendations * 45) +
            (log(1 + $globalRecommendations) * 25) +
            $ratingScore +
            $votesScore -
            ($negativeRecommendations * 25);
    }

    private function ownedGameIds(): Collection
    {
        return $this->ownedGameIdsCache
            ??= UserGameMeta::query()
                ->where('user_id', Auth::id())
                ->pluck('game_id')
                ->map(fn ($id) => (string) $id);
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
        int $friendRecommendations,
        int $globalRecommendations,
        ?float $averageRating
    ): string {
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

    private function steamHeaderUrl(string $gameId): string
    {
        return "https://cdn.cloudflare.steamstatic.com/steam/apps/{$gameId}/header.jpg";
    }
}