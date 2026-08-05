<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\IgdbImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IgdbGameSearchController extends Controller
{
    public function index(
        Request $request,
        IgdbImageService $images
    ): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = trim((string) ($data['q'] ?? ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $normalizedQuery = $this->normalize($query);

        $games = Game::query()
            ->where('source', 'igdb')
            ->where('normalized_title', 'LIKE', $normalizedQuery . '%')
            ->orderByRaw('igdb_cover_url IS NULL')
            ->orderByRaw("
                CASE
                    WHEN normalized_title = ? THEN 0
                    WHEN normalized_title LIKE ? THEN 1
                    ELSE 2
                END
            ", [$normalizedQuery, $normalizedQuery . '%'])
            ->limit(15)
            ->get([
                'id',
                'igdb_id',
                'title',
                'slug',
                'summary',
                'igdb_cover_url',
                'release_date',
            ])
            ->values();

        $imageData = $images->forGames($games->pluck('igdb_id')->all());

        return response()->json($games->map(fn (Game $game) => [
                'id' => $game->id,
                'igdb_id' => $game->igdb_id,
                'title' => $game->title,
                'slug' => $game->slug,
                'description' => $game->summary,
                'igdb_url' => $game->slug
                    ? 'https://www.igdb.com/games/' . $game->slug
                    : null,
                'cover_url' => data_get($imageData, "{$game->igdb_id}.cover_url")
                    ?: $game->igdb_cover_url,
                'header_image_url' => data_get(
                    $imageData,
                    "{$game->igdb_id}.header_image_url"
                ),
                'release_date' => $game->release_date?->format('Y-m-d'),
        ])->values());
    }

    private function normalize(string $title): string
    {
        return str($title)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->replaceMatches('/\b(the|game|edition|standard|deluxe|ultimate|complete)\b/', '')
            ->squish()
            ->toString();
    }
}
