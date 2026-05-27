<?php

namespace App\Services;

use App\Models\PublicReview;
use App\Models\UserConnection;
use App\Models\UserGameMeta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RecommendationService
{
    public function backlogRecommendations(): array
    {
        $userId = Auth::id();

        $ownedGameIds = UserGameMeta::query()
            ->where('user_id', $userId)
            ->pluck('game_id');

        return $this->buildRecommendations()

            ->whereIn(
                'game_id',
                $ownedGameIds
            )

            ->sortByDesc('score')

            ->take(10)

            ->values()

            ->toArray();
    }

    public function steamRecommendations(): array
    {
        $userId = Auth::id();

        $ownedGameIds = UserGameMeta::query()
            ->where('user_id', $userId)
            ->pluck('game_id');

        return $this->buildRecommendations()

            ->whereNotIn(
                'game_id',
                $ownedGameIds
            )

            ->sortByDesc('score')

            ->take(10)

            ->values()

            ->toArray();
    }

    public function friendsRanking(): array
    {
        return $this->buildRecommendations()

            ->where(
                'friend_recommendations',
                '>',
                0
            )

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
        $userId = Auth::id();

        $friendIds = $this->friendIds(
            $userId
        );

        return PublicReview::query()

            ->with([
                'user',
                'votes',
            ])

            ->where(
                'recommended',
                true
            )

            ->get()

            ->groupBy('game_id')

            ->map(function (
                $reviews,
                $gameId
            ) use (
                $friendIds
            ) {

                $first =
                    $reviews->first();

                $friendReviews =
                    $reviews->whereIn(
                        'user_id',
                        $friendIds
                    );

                $friendRecommendations =
                    $friendReviews
                        ->where(
                            'recommended',
                            true
                        )
                        ->count();

                $globalRecommendations =
                    $reviews
                        ->where(
                            'recommended',
                            true
                        )
                        ->count();

                $notRecommendedCount =
                    $reviews
                        ->where(
                            'not_recommended',
                            true
                        )
                        ->count();

                $averageRating =
                    round(
                        $reviews
                            ->whereNotNull(
                                'rating'
                            )
                            ->avg(
                                'rating'
                            ),
                        1
                    );

                $votesScore =
                    $reviews->sum(
                        fn ($review) =>
                            $review
                                ->votes
                                ->sum(
                                    'value'
                                )
                    );

                $score =

                    (
                        $friendRecommendations
                        * 40
                    )

                    +

                    (
                        $globalRecommendations
                        * 8
                    )

                    +

                    (
                        $averageRating
                        * 6
                    )

                    +

                    (
                        $votesScore
                        * 3
                    )

                    -

                    (
                        $notRecommendedCount
                        * 30
                    );

                return [

                    'game_id' =>
                        $gameId,

                    'score' =>
                        $score,

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

                        'id' =>
                            $gameId,

                        'title' =>
                            $first->title,

                        'header_image_url' =>

                            "https://cdn.cloudflare.steamstatic.com/steam/apps/{$gameId}/header.jpg",
                    ],

                    'reason' =>

                        $this->reasonText(
                            $friendRecommendations,
                            $globalRecommendations,
                            $averageRating
                        ),
                ];
            })

            ->sortByDesc('score')

            ->values();
    }

    private function friendIds(
        int $userId
    ): Collection {

        return UserConnection::query()

            ->where(function (
                $query
            ) use (
                $userId
            ) {

                $query

                    // FRIENDS SENT
                    ->where(function (
                        $query
                    ) use (
                        $userId
                    ) {

                        $query
                            ->where(
                                'type',
                                'friend'
                            )

                            ->where(
                                'status',
                                'accepted'
                            )

                            ->where(
                                'sender_id',
                                $userId
                            );
                    })

                    // FRIENDS RECEIVED
                    ->orWhere(function (
                        $query
                    ) use (
                        $userId
                    ) {

                        $query
                            ->where(
                                'type',
                                'friend'
                            )

                            ->where(
                                'status',
                                'accepted'
                            )

                            ->where(
                                'receiver_id',
                                $userId
                            );
                    })

                    // FOLLOWING
                    ->orWhere(function (
                        $query
                    ) use (
                        $userId
                    ) {

                        $query
                            ->where(
                                'type',
                                'follow'
                            )

                            ->where(
                                'sender_id',
                                $userId
                            );
                    });
            })

            ->get()

            ->flatMap(function (
                $connection
            ) {

                return [

                    $connection->sender_id,

                    $connection->receiver_id,
                ];
            })

            ->unique()

            ->values();
    }

    private function reasonText(
        int $friendRecommendations,
        int $globalRecommendations,
        float $averageRating
    ): string {

        if (
            $friendRecommendations >= 3
        ) {

            return
                'Your friends highly recommend this game.';
        }

        if (
            $averageRating >= 8
        ) {

            return
                'Players consistently rate this game very highly.';
        }

        if (
            $globalRecommendations >= 10
        ) {

            return
                'One of the most recommended games right now.';
        }

        return
            'Trending in the community.';
    }
}