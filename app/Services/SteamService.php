<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

    public function getOwnedGame(
        string $steamId,
        int|string $appId
    ): ?array {
        return collect(
            $this->getOwnedGames($steamId)
        )->first(
            fn ($game) =>
                (string) $game['appid'] === (string) $appId
        );
    }

    public function getPlayerAchievements(
        string $steamId,
        int|string $appId
    ): array {
        return Cache::remember(
            "steam-achievements:{$steamId}:{$appId}",
            now()->addHours(6),
            function () use ($steamId, $appId) {
                $response = Http::get(
                    'https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v0001/',
                    [
                        'key' => config('services.steam.key'),
                        'steamid' => $steamId,
                        'appid' => $appId,
                        'l' => 'english',
                    ]
                );

                if (! $response->successful()) {
                    return [
                        'unlocked' => null,
                        'total' => null,
                    ];
                }

                $achievements =
                    $response->json('playerstats.achievements')
                    ?? [];

                return [
                    'unlocked' => collect($achievements)
                        ->where('achieved', 1)
                        ->count(),

                    'total' => count($achievements),
                ];
            }
        );
    }

    public function achievementStats(
        string $steamId,
        int|string $appId
    ): array {
        $achievements = $this->getPlayerAchievements($steamId, $appId);

        $unlocked = $achievements['unlocked'];
        $total = $achievements['total'];

        if (! $total || $unlocked === null) {
            return [
                'unlocked' => 0,
                'total' => 0,
                'percent' => 0,
            ];
        }

        return [
            'unlocked' => $unlocked,
            'total' => $total,
            'percent' => (int) round(($unlocked / $total) * 100),
        ];
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

        return collect(
            $response->json('items') ?? []
        )
            ->map(fn ($game) => [
                'appid' =>
                    $game['id'] ?? null,

                'title' =>
                    $game['name'] ?? null,

                'cover_url' =>
                    $game['tiny_image']
                    ?? null,
            ])
            ->filter(fn ($game) =>
                $game['appid'] &&
                $game['title']
            )
            ->values()
            ->all();
    }

    public function getPlayerSummary(
        string $steamId
    ): ?array {
        $response = Http::get(
            'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/',
            [
                'key' => config('services.steam.key'),
                'steamids' => $steamId,
                'format' => 'json',
            ]
        );

        return $response->json(
            'response.players.0'
        );
    }

    public function getAppDetails(
        int|string $appId
    ): ?array {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0',
        ])->get(
            'https://store.steampowered.com/api/appdetails',
            [
                'appids' => $appId,
                'l' => 'english',
                'cc' => 'US',
            ]
        );

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json(
            "{$appId}.data"
        );

        if (! is_array($data)) {
            return null;
        }

        return $data;
    }

    public function searchPlayer(string $query): array
    {
        $steamId = $this->extractSteamId($query);

        if (! $steamId) {
            $steamId = $this->resolveVanityUrl($query);
        }

        if (! $steamId) {
            return [];
        }

        return $this->playerSummary($steamId);
    }

    private function extractSteamId(string $query): ?string
    {
        $query = trim($query);

        if (preg_match('/steamcommunity\.com\/profiles\/(\d+)/', $query, $matches)) {
            return $matches[1];
        }

        if (preg_match('/7656\d{13}/', $query, $matches)) {
            return $matches[0];
        }

        return null;
    }

    private function resolveVanityUrl(string $query): ?string
    {
        $vanity = trim($query);

        if (Str::contains($vanity, 'steamcommunity.com/id/')) {
            $vanity = Str::after($vanity, 'steamcommunity.com/id/');
            $vanity = trim($vanity, '/');
        }

        if (Str::contains($vanity, '/')) {
            return null;
        }

        $response = Http::get(
            'https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/',
            [
                'key' => config('services.steam.key'),
                'vanityurl' => $vanity,
            ]
        );

        if (! $response->successful()) {
            return null;
        }

        return data_get($response->json(), 'response.steamid');
    }

    private function playerSummary(string $steamId): array
    {
        $response = Http::get(
            'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/',
            [
                'key' => config('services.steam.key'),
                'steamids' => $steamId,
            ]
        );

        if (! $response->successful()) {
            return [];
        }

        return collect(
            data_get($response->json(), 'response.players', [])
        )
            ->map(fn ($player) => [
                'steam_id' => $player['steamid'] ?? null,
                'name' => $player['personaname'] ?? null,
                'avatar' => $player['avatarfull'] ?? null,
                'profile_url' => $player['profileurl'] ?? null,
            ])
            ->filter(fn ($player) => filled($player['steam_id']))
            ->values()
            ->toArray();
    }
}