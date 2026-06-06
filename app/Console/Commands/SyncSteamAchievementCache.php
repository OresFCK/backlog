<?php

namespace App\Console\Commands;

use App\Models\SteamGameAchievementCache;
use App\Models\User;
use App\Services\SteamService;
use Illuminate\Console\Command;

class SyncSteamAchievementCache extends Command
{
    protected $signature = 'steam:sync-achievements {--user_id=}';

    protected $description = 'Sync Steam achievement stats into local cache';

    public function handle(SteamService $steam): int
    {
        $users = User::query()
            ->whereNotNull('steam_id')
            ->when($this->option('user_id'), fn ($query, $userId) =>
                $query->where('id', $userId)
            )
            ->get();

        foreach ($users as $user) {
            $this->info("Syncing achievements for user {$user->id}");

            $games = $steam->getOwnedGames($user->steam_id);

            foreach ($games as $game) {
                $appId = $game['appid'] ?? null;

                if (! $appId) {
                    continue;
                }

                $stats = $steam->achievementStats($user->steam_id, $appId);

                SteamGameAchievementCache::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'steam_app_id' => $appId,
                    ],
                    [
                        'steam_id' => $user->steam_id,
                        'unlocked' => $stats['unlocked'],
                        'total' => $stats['total'],
                        'percent' => $stats['percent'],
                        'synced_at' => now(),
                    ]
                );
            }
        }

        return self::SUCCESS;
    }
}