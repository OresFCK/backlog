<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IgdbGameSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q'));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $games = Game::query()
            ->where('source', 'igdb')
            ->where(function ($builder) use ($query) {
                $builder
                    ->where('title', 'ilike', "%{$query}%")
                    ->orWhere('normalized_title', 'ilike', "%{$this->normalize($query)}%");
            })
            ->orderByRaw("
                CASE
                    WHEN lower(title) = lower(?) THEN 0
                    WHEN lower(title) LIKE lower(?) THEN 1
                    ELSE 2
                END
            ", [$query, "{$query}%"])
            ->limit(15)
            ->get([
                'id',
                'igdb_id',
                'title',
                'slug',
                'cover_url',
                'release_date',
            ])
            ->map(fn (Game $game) => [
                'id' => $game->id,
                'igdb_id' => $game->igdb_id,
                'title' => $game->title,
                'slug' => $game->slug,
                'cover_url' => $game->cover_url,
                'release_date' => $game->release_date?->format('Y-m-d'),
            ])
            ->values();

        return response()->json($games);
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