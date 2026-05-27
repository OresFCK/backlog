<?php

namespace App\Services;

use App\Models\PublicReview;
use App\Models\UserConnection;
use App\Models\UserGameMeta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RecommendationService
{
    /**
     * Games user already owns but should play from backlog
     */
    public function backlogRecommendations(): array
    {
        $userId = Auth::id();

        $ownedGameIds = UserGameMeta::where('user_id', $userId)
            ->pluck('game_id');

        return $this->buildRecommendations()
            ->whereIn('game_id', $ownedGameIds)
            ->sortByDesc('score')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Games user does NOT own yet
     */
    public function steamRecommendations(): array
    {
        $userId = Auth::id();

        $ownedGameIds = UserGameMeta::where('user_id', $userId)
            ->pluck('game_id');

        return $this->buildRecommendations()
            ->whereNotIn('game_id', $ownedGameIds)
            ->sortByDesc('score')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Ranking based only on friends recommendations
     */
    public function friendsRanking(): array
    {
        return $this->buildRecommendations()
            ->where('friend_recommendations', '>', 0)
            ->sortByDesc('score')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Overall global ranking
     */
    public function globalRanking(): array
    {
        return $this->buildRecommendations()
            ->sortByDesc('score')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Main recommendation engine
     */
    private function buildRecommendations(): Collection
    {
        $userId = Auth::id();

        $friendIds = $this->friendIds($userId);

        return PublicReview::query()
            ->with(['user', 'votes'])
            ->where('recommended', true)
            ->get()

            // Group all reviews by game
            ->groupBy('game_id')

            ->map(function ($reviews, $gameId) use ($friendIds) {

                $firstReview = $reviews->first();

                // Reviews from friends only
                $friendReviews = $reviews->whereIn(
                    'user_id',
                    $friendIds
                );

                $friendRecommendations = $friendReviews
                    ->where('recommended', true)
                    ->count();

                $globalRecommendations = $reviews
                    ->where('recommended', true)
                    ->count();

                $notRecommendedCount = $reviews
                    ->where('not_recommended', true)
                    ->count();

                $averageRating = round(
                    $reviews
                        ->whereNotNull('rating')
                        ->avg('rating'),
                    1
                );

                // Sum all votes from all reviews
                $votesScore = $reviews->sum(
                    fn ($review) => $review->votes->sum('value')
                );

                /**
                 * Final recommendation score
                 */
                $score =
                    ($friendRecommendations * 40) +
                    ($globalRecommendations * 8) +
                    ($averageRating * 6) +
                    ($votesScore * 3) -
                    ($notRecommendedCount * 30);

                return [
                    'game_id' => $gameId,

                    'score' => $score,

                    'friend_recommendations' =>
                        $friendRecommendations,

                    'global_recommendations' =>
                        $globalRecommendations,

                    'not_recommended_count' =>
                        $notRecommendedCount,

                    'average_rating' =>
                        $averageRating,

                    'votes_score' =>
                        $votesScore,

                    'game' => [
                        'id' => $gameId,

                        'title' =>

                            $firstReview->game_title
                            ?? $firstReview->game?->title
                            ?? "Game {$gameId}",

                        'header_image_url' =>
                            "https://cdn.cloudflare.steamstatic.com/steam/apps/{$gameId}/header.jpg",
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

    /**
     * Get all friend/followed user IDs
     */
    private function friendIds(int $userId): Collection
    {
        return UserConnection::query()

            ->where(function ($query) use ($userId) {

                // Accepted friend requests sent by user
                $query->where(function ($query) use ($userId) {

                    $query
                        ->where('type', 'friend')
                        ->where('status', 'accepted')
                        ->where('sender_id', $userId);

                })

                // Accepted friend requests received by user
                ->orWhere(function ($query) use ($userId) {

                    $query
                        ->where('type', 'friend')
                        ->where('status', 'accepted')
                        ->where('receiver_id', $userId);

                })

                // Following users
                ->orWhere(function ($query) use ($userId) {

                    $query
                        ->where('type', 'follow')
                        ->where('sender_id', $userId);

                });
            })

            ->get()

            ->flatMap(function ($connection) {

                return [
                    $connection->sender_id,
                    $connection->receiver_id,
                ];
            })

            ->unique()
            ->values();
    }

    /**
     * Human-readable recommendation reason
     */
    private function reasonText(
        int $friendRecommendations,
        int $globalRecommendations,
        float $averageRating
    ): string {

        if ($friendRecommendations >= 3) {
            return 'Your friends highly recommend this game.';
        }

        if ($averageRating >= 8) {
            return 'Players consistently rate this game very highly.';
        }

        if ($globalRecommendations >= 10) {
            return 'One of the most recommended games right now.';
        }

        return 'Trending in the community.';
    }
}