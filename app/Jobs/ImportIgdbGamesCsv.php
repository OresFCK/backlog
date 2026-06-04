<?php

namespace App\Jobs;

use App\Helpers\GameTitleNormalizer;
use App\Models\Game;
use App\Services\IgdbDumpService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ImportIgdbGamesCsv implements ShouldQueue
{
    use Queueable;

    public int $timeout = 7200;

    public function handle(IgdbDumpService $igdb): void
    {
        $dump = $igdb->getDump('games');

        $handle = fopen($dump['s3_url'], 'r');

        if (! $handle) {
            throw new \RuntimeException('Unable to open IGDB CSV stream.');
        }

        $headers = fgetcsv($handle);

        if (! $headers) {
            fclose($handle);
            throw new \RuntimeException('Invalid CSV headers.');
        }

        $batch = [];
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($headers)) {
                $skipped++;
                continue;
            }

            $data = array_combine($headers, $row);

            if (empty($data['id']) || empty($data['name'])) {
                continue;
            }

            $batch[] = [
                'igdb_id' => (int) $data['id'],
                'title' => $data['name'],
                'normalized_title' => GameTitleNormalizer::normalize($data['name']),
                'slug' => $data['slug'] ?? null,
                'summary' => $data['summary'] ?? null,
                'source' => 'igdb',
                'release_date' => ! empty($data['first_release_date'])
                    ? date('Y-m-d', (int) $data['first_release_date'])
                    : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 1000) {
                $this->upsertBatch($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            $this->upsertBatch($batch);
        }

        fclose($handle);

        Log::info('IGDB games import finished', [
            'skipped_rows' => $skipped,
        ]);
    }

    private function upsertBatch(array $batch): void
    {
        Game::upsert(
            $batch,
            ['igdb_id'],
            [
                'title',
                'normalized_title',
                'slug',
                'summary',
                'release_date',
                'source',
                'updated_at',
            ]
        );
    }
}