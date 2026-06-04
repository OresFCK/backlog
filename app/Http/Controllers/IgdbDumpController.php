<?php

namespace App\Http\Controllers;

use App\Jobs\ImportIgdbCoversCsv;
use App\Jobs\ImportIgdbGamesCsv;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;

class IgdbDumpController extends Controller
{
    public function syncCatalog(): JsonResponse
    {
        Bus::chain([
            new ImportIgdbGamesCsv(),
            new ImportIgdbCoversCsv(),
        ])->dispatch();

        return response()->json([
            'message' => 'IGDB sync queued.',
        ]);
    }
}