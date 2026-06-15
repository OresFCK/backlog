<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\PublicReview;
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

                'genres' => [],
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
}