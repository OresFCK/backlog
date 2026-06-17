<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSteamGame;

class SteamLibrarySyncService
{
    public function __construct(
        private SteamService $steam
    ) {}

    public function sync(User $user): int
    {
        if (! $user->steam_id) {
            return 0;
        }

        $games = $this->steam->getOwnedGames($user->steam_id);

        if (empty($games)) {
            return 0;
        }

        $count = 0;

        foreach ($games as $game) {
            if (empty($game['appid'])) {
                continue;
            }

            UserSteamGame::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'steam_app_id' => (string) $game['appid'],
                ],
                [
                    'name' => $game['name'] ?? null,
                    'playtime_forever' => (int) ($game['playtime_forever'] ?? 0),
                    'last_synced_at' => now(),
                ]
            );

            $count++;
        }

        return $count;
    }
}