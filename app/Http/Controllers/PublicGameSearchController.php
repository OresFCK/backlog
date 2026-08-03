<?php

namespace App\Http\Controllers;

use App\Helpers\GameTitleNormalizer;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

        $normalizedQuery = GameTitleNormalizer::normalize($query);

        if ($normalizedQuery === '') {
            return response()->json([]);
        }

        $games = $this->searchGames($normalizedQuery);

        if ($games->count() < 8) {
            $games = $games
                ->concat($this->searchGames($normalizedQuery, true))
                ->unique(fn (Game $game) => $game->normalized_title ?: $game->id)
                ->take(8)
                ->values();
        }

        $games = $games
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
            });

        return response()->json($games);
    }

    private function searchGames(
        string $query,
        bool $contains = false
    ): Collection
    {
        return Game::query()
            ->whereNotNull('slug')
            ->where(
                'normalized_title',
                'like',
                $contains ? "%{$query}%" : "{$query}%"
            )
            ->orderByRaw(
                'CASE WHEN normalized_title = ? THEN 0 ELSE 1 END',
                [$query]
            )
            ->orderByRaw(
                'CASE WHEN COALESCE(cover_url, igdb_cover_url, '
                .'header_image_url) IS NULL THEN 1 ELSE 0 END'
            )
            ->orderBy('title')
            ->limit(24)
            ->get()
            ->unique(fn (Game $game) => $game->normalized_title ?: $game->id)
            ->take(8)
            ->values();
    }
}
