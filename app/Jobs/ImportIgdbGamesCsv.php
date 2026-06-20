<?php

namespace App\Jobs;

use App\Helpers\GameTitleNormalizer;
use App\Models\Game;
use App\Services\IgdbDumpService;
use Carbon\Carbon;
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
            throw new \RuntimeException('Unable to open IGDB games CSV stream.');
        }

        $headers = fgetcsv($handle);

        if (! $headers) {
            fclose($handle);

            throw new \RuntimeException('Invalid IGDB games CSV headers.');
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

                'igdb_cover_id' => ! empty($data['cover'])
                    ? (int) $data['cover']
                    : null,

                'release_date' => $this->parseReleaseDate(
                    $data['first_release_date'] ?? null
                ),

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

    private function parseReleaseDate(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            $timestamp = (int) $value;

            if ($timestamp <= 0) {
                return null;
            }

            return Carbon::createFromTimestamp(
                $timestamp
            )->toDateString();
        }

        try {
            return Carbon::parse($value)
                ->toDateString();
        } catch (\Throwable) {
            return null;
        }
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
                'source',
                'igdb_cover_id',
                'release_date',
                'updated_at',
            ]
        );
    }
}