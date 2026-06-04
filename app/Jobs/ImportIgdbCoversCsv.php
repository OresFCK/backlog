<?php

namespace App\Jobs;

use App\Models\Game;
use App\Services\IgdbDumpService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ImportIgdbCoversCsv implements ShouldQueue
{
    use Queueable;

    public int $timeout = 7200;

    public function handle(IgdbDumpService $igdb): void
    {
        $dump = $igdb->getDump('covers');

        $handle = fopen($dump['s3_url'], 'r');

        if (! $handle) {
            throw new \RuntimeException('Unable to open IGDB covers CSV stream.');
        }

        $headers = fgetcsv($handle);

        if (! $headers) {
            fclose($handle);

            throw new \RuntimeException('Invalid IGDB covers CSV headers.');
        }

        $batch = [];
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($headers)) {
                $skipped++;
                continue;
            }

            $data = array_combine($headers, $row);

            $coverId = ! empty($data['id'])
                ? (int) $data['id']
                : null;

            $imageId = $data['image_id'] ?? null;

            if (! $coverId || ! $imageId) {
                continue;
            }

            $batch[$coverId] = [
                'image_id' => $imageId,
                'url' => "https://images.igdb.com/igdb/image/upload/t_cover_big/{$imageId}.jpg",
            ];

            if (count($batch) >= 1000) {
                $this->updateBatch($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            $this->updateBatch($batch);
        }

        fclose($handle);

        Log::info('IGDB covers import finished', [
            'skipped_rows' => $skipped,
        ]);
    }

    private function updateBatch(array $batch): void
    {
        foreach ($batch as $coverId => $cover) {
            Game::where('igdb_cover_id', $coverId)
                ->update([
                    'igdb_cover_image_id' => $cover['image_id'],
                    'igdb_cover_url' => $cover['url'],
                ]);
        }
    }
}