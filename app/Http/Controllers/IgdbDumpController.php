<?php

namespace App\Http\Controllers;

use App\Services\IgdbDumpService;
use Illuminate\Http\JsonResponse;

class IgdbDumpController extends Controller
{
    public function show(string $endpoint, IgdbDumpService $igdb): JsonResponse
    {
        return response()->json($igdb->getDump($endpoint));
    }

    public function importGames()
    {
        ImportIgdbGamesCsv::dispatch();

        return response()->json([
            'message' => 'Import IGDB games został dodany do kolejki.',
        ]);
    }
}