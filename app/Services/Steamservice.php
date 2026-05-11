<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SteamService
{
    public function getOwnedGames(string $steamId): array
    {
        $response = Http::get('https://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/', [
            'key' => config('services.steam.key'),
            'steamid' => $steamId,
            'include_appinfo' => true,
            'include_played_free_games' => true,
            'format' => 'json',
        ]);

        return $response->json('response.games') ?? [];
    }

    public function getPlayerSummary(string $steamId): ?array
    {
        $response = Http::get('https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/', [
            'key' => config('services.steam.key'),
            'steamids' => $steamId,
            'format' => 'json',
        ]);

        return $response->json('response.players.0');
    }
}