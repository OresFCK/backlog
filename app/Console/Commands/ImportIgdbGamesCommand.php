<?php

namespace App\Console\Commands;

use App\Jobs\ImportIgdbGamesCsv;
use App\Services\IgdbDumpService;
use Illuminate\Console\Command;

class ImportIgdbGamesCommand extends Command
{
    protected $signature = 'igdb:import-games';

    protected $description = 'Import IGDB games CSV directly';

    public function handle(IgdbDumpService $igdb): int
    {
        $this->info('Starting IGDB games import...');

        (new ImportIgdbGamesCsv())->handle($igdb);

        $this->info('IGDB games import finished.');

        return self::SUCCESS;
    }
}