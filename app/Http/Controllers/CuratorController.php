<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\Game;
use App\Models\PublicReview;
use App\Services\SteamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CuratorController extends Controller
{
    public function index(SteamService $steam): Response
    {
        return Inertia::render('curators/index', [
            ...Payload::pageData($steam),
        ]);
    }

    public function showGame(string $source, string $gameId): JsonResponse
    {
        dd(
    'request',
    [
        'source' => $source,
        'gameId' => $gameId,
    ],
    'reviews',
    PublicReview::query()
        ->select('id', 'source', 'game_id', 'game_title', 'title', 'is_public')
        ->latest()
        ->limit(10)
        ->get()
        ->toArray()
);
        $game = Game::query()
            ->where('source', $source)
            ->where('game_id', $gameId)
            ->first();

        $reviews = PublicReview::query()
            ->with('user')
            ->where('is_public', true)
            ->where(function ($query) use ($source, $gameId, $game) {
                $query->where(function ($query) use ($source, $gameId) {
                    $query
                        ->where('source', $source)
                        ->where('game_id', $gameId);
                });

                if ($game?->name) {
                    $query->orWhere('game_title', $game->name);
                }
            })
            ->latest()
            ->get();

        $reviewCount = $reviews->count();
        $recommendedCount = $reviews->where('recommended', true)->count();

        return response()->json([
            'source' => $source,
            'game_id' => $gameId,

            'average_rating' => $reviewCount
                ? round($reviews->avg('rating'), 1)
                : null,

            'reviews_count' => $reviewCount,

            'recommended_percent' => $reviewCount
                ? round(($recommendedCount / $reviewCount) * 100)
                : null,

            'reviews' => $reviews
                ->map(fn (PublicReview $review) => [
                    'id' => $review->id,
                    'title' => $review->title,
                    'body' => $review->body,
                    'rating' => $review->rating,
                    'platform' => $review->platform,
                    'screenshot_url' => $review->screenshot_path
                        ? Storage::url($review->screenshot_path)
                        : null,
                    'recommended' => $review->recommended,
                    'not_recommended' => $review->not_recommended,
                    'created_at' => $review->created_at?->diffForHumans(),

                    'user' => [
                        'id' => $review->user?->id,
                        'name' => $review->user?->visible_name,
                        'avatar' => $review->user?->steam_avatar_url,
                    ],
                ])
                ->values()
                ->toArray(),
        ]);
    }
}