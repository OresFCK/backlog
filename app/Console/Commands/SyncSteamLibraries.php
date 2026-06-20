<?php

namespace App\Console\Commands;

use App\Helpers\CacheKeys;
use App\Models\User;
use App\Services\SteamLibrarySyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncSteamLibraries extends Command
{
    protected $signature = 'steam:libraries-sync';

    protected $description = 'Sync Steam libraries for users';

    public function handle(SteamLibrarySyncService $sync): int
    {
        User::query()
            ->whereNotNull('steam_id')
            ->chunkById(100, function ($users) use ($sync) {
                foreach ($users as $user) {
                    $sync->sync($user);

                    Cache::forget(CacheKeys::userLibrary($user->id));
                    Cache::forget(CacheKeys::userLibraryForUser($user->id));
                    Cache::forget(CacheKeys::profilePage($user->id));
                    Cache::forget(CacheKeys::publicProfile($user->id));

                    foreach (['Backlog', 'Playing', 'Finished', 'Dropped'] as $status) {
                        Cache::forget(CacheKeys::userLibraryStatus($user->id, $status));
                    }
                }
            });

        $this->info('Steam libraries synced.');

        return self::SUCCESS;
    }
}