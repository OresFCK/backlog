<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\IgdbDumpService;
use App\Services\IgdbImageService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class IgdbGameSearchController extends Controller
{
    public function index(
        Request $request,
        IgdbImageService $images,
        IgdbDumpService $igdb
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

        $results = $games->map(fn (Game $game) => [
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
        ])->values();

        if ($results->count() < 15) {
            try {
                $liveResults = collect($igdb->searchGames($query, 15))
                    ->map(fn (array $game) => $this->mapLiveGame($game));

                $results = $results
                    ->concat($liveResults)
                    ->unique(fn (array $game) => (string) $game['igdb_id'])
                    ->take(15)
                    ->values();
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return response()->json($results);
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

    private function mapLiveGame(array $game): array
    {
        $companies = collect($game['involved_companies'] ?? []);
        $coverImageId = data_get($game, 'cover.image_id');
        $headerImageId = data_get($game, 'screenshots.0.image_id');

        return [
            'id' => 'igdb-'.$game['id'],
            'igdb_id' => $game['id'],
            'title' => $game['name'] ?? 'Unknown game',
            'slug' => $game['slug'] ?? null,
            'description' => $game['summary'] ?? null,
            'developer' => data_get(
                $companies->firstWhere('developer', true),
                'company.name'
            ),
            'publisher' => data_get(
                $companies->firstWhere('publisher', true),
                'company.name'
            ),
            'igdb_url' => isset($game['slug'])
                ? 'https://www.igdb.com/games/'.$game['slug']
                : null,
            'cover_url' => $coverImageId
                ? "https://images.igdb.com/igdb/image/upload/t_cover_big/{$coverImageId}.jpg"
                : null,
            'header_image_url' => $headerImageId
                ? "https://images.igdb.com/igdb/image/upload/t_screenshot_big/{$headerImageId}.jpg"
                : null,
            'release_date' => isset($game['first_release_date'])
                ? CarbonImmutable::createFromTimestampUTC((int) $game['first_release_date'])->format('Y-m-d')
                : null,
        ];
    }
}
