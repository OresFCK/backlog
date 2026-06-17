<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SteamLibrarySyncService;
use Illuminate\Console\Command;

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
                }
            });

        return self::SUCCESS;
    }
}