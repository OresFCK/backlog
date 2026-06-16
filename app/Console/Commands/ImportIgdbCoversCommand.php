<?php

namespace App\Console\Commands;

use App\Jobs\ImportIgdbCoversCsv;
use App\Services\IgdbDumpService;
use Illuminate\Console\Command;

class ImportIgdbCoversCommand extends Command
{
    protected $signature = 'igdb:import-covers';

    protected $description = 'Import IGDB covers CSV directly';

    public function handle(IgdbDumpService $igdb): int
    {
        $this->info('Starting IGDB covers import...');

        (new ImportIgdbCoversCsv())->handle($igdb);

        $this->info('IGDB covers import finished.');

        return self::SUCCESS;
    }
}