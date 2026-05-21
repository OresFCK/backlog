<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchGameRequest;
use App\Http\Requests\StoreGameRequest;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    public function search(SearchGameRequest $request): JsonResponse
    {
        $query = $request->validated('q');

        $games = Game::query()
            ->where('title', 'ilike', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($games);
    }

    public function store(StoreGameRequest $request): JsonResponse
    {
        $data = $request->validated();

        $game = Game::firstOrCreate(
            $this->uniqueGameData($data),
            $data
        );

        return response()->json($game, 201);
    }

    private function uniqueGameData(array $data): array
    {
        return [
            'steam_app_id' => $data['steam_app_id'] ?? null,
            'title' => $data['title'],
        ];
    }
}