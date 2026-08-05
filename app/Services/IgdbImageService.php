<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class IgdbImageService
{
    public function __construct(private IgdbDumpService $igdb) {}

    public function forGame(int $igdbId): array
    {
        return $this->forGames([$igdbId])[$igdbId] ?? [];
    }

    public function forGames(array $igdbIds): array
    {
        $ids = collect($igdbIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if (
            $ids->isEmpty()
            || blank(config('services.igdb.client_id'))
            || blank(config('services.igdb.client_secret'))
        ) {
            return [];
        }

        return Cache::remember(
            'igdb:images:' . $ids->sort()->implode(','),
            now()->addDay(),
            function () use ($ids): array {
                try {
                    $response = Http::withHeaders([
                        'Client-ID' => config('services.igdb.client_id'),
                        'Authorization' => 'Bearer ' . $this->igdb->token(),
                    ])
                        ->withBody(
                            'fields id,cover.image_id,artworks.image_id,'
                            . 'screenshots.image_id; where id = ('
                            . $ids->implode(',') . '); limit 50;',
                            'text/plain'
                        )
                        ->post('https://api.igdb.com/v4/games');

                    $response->throw();

                    return collect($response->json())
                        ->mapWithKeys(function (array $game): array {
                            $coverId = data_get($game, 'cover.image_id');
                            $headerId = data_get($game, 'artworks.0.image_id')
                                ?: data_get($game, 'screenshots.0.image_id');

                            return [
                                (int) $game['id'] => array_filter([
                                    'cover_url' => $coverId
                                        ? $this->imageUrl($coverId, 'cover_big_2x')
                                        : null,
                                    'header_image_url' => $headerId
                                        ? $this->imageUrl($headerId, '1080p')
                                        : null,
                                ]),
                            ];
                        })
                        ->all();
                } catch (Throwable) {
                    return [];
                }
            }
        );
    }

    private function imageUrl(string $imageId, string $size): string
    {
        return "https://images.igdb.com/igdb/image/upload/t_{$size}/{$imageId}.jpg";
    }
}
