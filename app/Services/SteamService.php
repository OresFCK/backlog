<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SteamService
{
    public function getOwnedGames(string $steamId): array
    {
        $response = Http::get(
            'https://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/',
            [
                'key' => config('services.steam.key'),
                'steamid' => $steamId,
                'include_appinfo' => true,
                'include_played_free_games' => true,
                'format' => 'json',
            ]
        );

        return $response->json('response.games') ?? [];
    }

    public function getWishlist(string $steamId): array
    {
        $url = "https://store.steampowered.com/wishlist/profiles/{$steamId}/wishlistdata/?p=0";

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',

                'header' => implode("\r\n", [
                    'User-Agent: Mozilla/5.0',
                    'Accept: application/json,text/javascript,*/*;q=0.01',
                    'Accept-Language: en-US,en;q=0.9',
                    'Referer: https://store.steampowered.com/',
                ]),
            ],
        ]);

        $response = file_get_contents($url, false, $context);

        if (! $response) {
            return [];
        }

        $data = json_decode($response, true);

        if (! is_array($data)) {
            return [];
        }

        return collect($data)
            ->map(function ($game, $appid) {
                return [
                    'appid' => (int) $appid,

                    'name' => $game['name'] ?? 'Unknown game',

                    'playtime_forever' => null,

                    'capsule' => $game['capsule'] ?? null,

                    'review_score' => $game['review_score'] ?? null,
                ];
            })
            ->values()
            ->all();
    }

    public function searchStore(string $query): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0',
        ])->get(
            'https://store.steampowered.com/api/storesearch/',
            [
                'term' => $query,
                'l' => 'english',
                'cc' => 'US',
            ]
        );

        if (! $response->successful()) {
            return [];
        }

        return collect($response->json('items') ?? [])
            ->map(fn ($game) => [
                'appid' => $game['id'] ?? null,
                'title' => $game['name'] ?? null,
                'cover_url' => $game['tiny_image'] ?? null,
            ])
            ->filter(fn ($game) =>
                $game['appid'] &&
                $game['title']
            )
            ->values()
            ->all();
    }

    public function getPlayerSummary(string $steamId): ?array
    {
        $response = Http::get(
            'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/',
            [
                'key' => config('services.steam.key'),
                'steamids' => $steamId,
                'format' => 'json',
            ]
        );

        return $response->json('response.players.0');
    }
}