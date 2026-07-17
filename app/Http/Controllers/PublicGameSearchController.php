<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicGameSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = trim((string) ($data['q'] ?? ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $games = Game::query()
            ->whereNotNull('slug')
            ->where(function ($builder) use ($query) {
                $builder
                    ->where('title', 'like', "%{$query}%")
                    ->orWhere('slug', 'like', "%{$query}%")
                    ->orWhere('steam_app_id', 'like', "%{$query}%");
            })
            ->orderBy('title')
            ->limit(8)
            ->get()
            ->map(function (Game $game) {
                $coverUrl = $game->cover_url
                    ?? $game->igdb_cover_url
                    ?? $game->header_image_url;

                if ($coverUrl && str_starts_with($coverUrl, '//')) {
                    $coverUrl = 'https:' . $coverUrl;
                }

                return [
                    'id' => $game->id,
                    'title' => $game->title,
                    'slug' => $game->slug,
                    'steam_app_id' => $game->steam_app_id,
                    'cover_url' => $coverUrl,
                ];
            })
            ->values();

        return response()->json($games);
    }
}
