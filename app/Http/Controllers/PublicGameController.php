<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\PublicReview;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicGameController extends Controller
{
    public function show(Game $game): Response
    {
        $reviews = PublicReview::query()
            ->with(['user', 'votes'])
            ->where('game_id', $game->id)
            ->where('is_public', true)
            ->latest()
            ->get();

        $relatedGames = $this->relatedGames($game);

        return Inertia::render('games/public-show', [
            'game' => [
                'id' => $game->id,
                'database_id' => $game->id,
                'slug' => $game->slug,
                'title' => $game->title,

                'steam_app_id' => $game->steam_app_id,
                'igdb_id' => $game->igdb_id,
                'source' => $game->source,

                'summary' => $game->summary,
                'description' => $game->summary,

                'cover_url' => $game->cover_url
                    ?: $game->igdb_cover_url,

                'header_image_url' => $game->header_image_url
                    ?: $game->cover_url
                    ?: $game->igdb_cover_url,

                'release_date' => $game->release_date?->translatedFormat('M j, Y'),
                'metacritic_score' => $game->metacritic_score,
                'steam_rating_percent' => $game->steam_rating_percent,
                'average_playtime_minutes' => $game->average_playtime_minutes,

                'genres' => $this->genreNames(
                    $game->genres ?? []
                ),
            ],

            'reviews' => $reviews
                ->map(fn (PublicReview $review) => [
                    'id' => $review->id,
                    'title' => $review->title,
                    'body' => $review->body,
                    'rating' => $review->rating,
                    'platform' => $review->platform,
                    'recommended' => $review->recommended,
                    'not_recommended' => $review->not_recommended,
                    'time_to_beat_minutes' => $review->time_to_beat_minutes,

                    'screenshot_url' => $review->screenshot_path
                        ? Storage::url($review->screenshot_path)
                        : null,

                    'votes_score' => $review->votes->sum('value'),
                    'created_at' => $review->created_at?->diffForHumans(),

                    'user' => [
                        'name' => $review->user?->visible_name,
                        'avatar' => $review->user?->steam_avatar_url,
                        'steam_id' => $review->user?->steam_id,
                    ],
                ])
                ->values(),

            'stats' => [
                'total_reviews' => $reviews->count(),
                'average_rating' => round((float) $reviews->avg('rating'), 1),
                'recommended_count' => $reviews->where('recommended', true)->count(),
                'not_recommended_count' => $reviews->where('not_recommended', true)->count(),
                'platforms' => $reviews
                    ->whereNotNull('platform')
                    ->groupBy('platform')
                    ->map->count(),
            ],

            'relatedGames' => $relatedGames,

            'auth' => [
                'user' => auth()->user()
                    ? [
                        'name' => auth()->user()->visible_name,
                        'avatar' => auth()->user()->steam_avatar_url,
                    ]
                    : null,
            ],
        ]);
    }

    private function relatedGames(Game $game): Collection
    {
        $gameGenres = collect($game->genres ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($gameGenres->isEmpty()) {
            return collect();
        }

        return Game::query()
            ->where('id', '!=', $game->id)
            ->whereNotNull('slug')
            ->whereNotNull('genres')
            ->where(function ($query) {
                $query
                    ->whereNotNull('cover_url')
                    ->orWhereNotNull('igdb_cover_url');
            })
            ->limit(500)
            ->get()
            ->map(function (Game $relatedGame) use ($gameGenres) {
                $relatedGenres = collect($relatedGame->genres ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter();

                $matchingGenresCount = $relatedGenres
                    ->intersect($gameGenres)
                    ->count();

                return [
                    'game' => $relatedGame,
                    'matching_genres_count' => $matchingGenresCount,
                ];
            })
            ->filter(fn (array $item) => $item['matching_genres_count'] > 0)
            ->sortByDesc('matching_genres_count')
            ->take(12)
            ->map(fn (array $item) => [
                'id' => $item['game']->id,
                'slug' => $item['game']->slug,
                'title' => $item['game']->title,
                'name' => $item['game']->title,

                'cover_url' => $item['game']->cover_url
                    ?: $item['game']->igdb_cover_url,

                'score' => $item['game']->metacritic_score
                    ?: $item['game']->steam_rating_percent,

                'metacritic_score' => $item['game']->metacritic_score,
                'steam_rating_percent' => $item['game']->steam_rating_percent,
                'matching_genres_count' => $item['matching_genres_count'],

                'genres' => $this->genreNames(
                    $item['game']->genres ?? []
                ),
            ])
            ->values();
    }

    private function genreNames(array $genreIds): array
    {
        $labels = [
            2 => 'Point-and-click',
            4 => 'Fighting',
            5 => 'Shooter',
            7 => 'Music',
            8 => 'Platform',
            9 => 'Puzzle',
            10 => 'Racing',
            11 => 'RTS',
            12 => 'RPG',
            13 => 'Simulator',
            14 => 'Sport',
            15 => 'Strategy',
            16 => 'TBS',
            24 => 'Tactical',
            26 => 'Quiz',
            30 => 'Pinball',
            31 => 'Adventure',
            32 => 'Indie',
            33 => 'Arcade',
            34 => 'Visual Novel',
            35 => 'Card & Board Game',
            36 => 'MOBA',
        ];

        return collect($genreIds)
            ->map(fn ($id) => $labels[(int) $id] ?? null)
            ->filter()
            ->values()
            ->all();
    }
}