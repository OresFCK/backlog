<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $games = Game::query()
            ->where('title', 'ilike', '%' . $validated['q'] . '%') // PostgreSQL
            ->limit(10)
            ->get();

        return response()->json($games);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'steam_app_id' => ['nullable', 'integer'],
            'cover_url' => ['nullable', 'url'],
            'header_image_url' => ['nullable', 'url'],
            'release_date' => ['nullable', 'date'],
            'metacritic_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'steam_rating_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'average_playtime_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $game = Game::firstOrCreate(
            [
                'steam_app_id' => $validated['steam_app_id'] ?? null,
                'title' => $validated['title'],
            ],
            $validated
        );

        return response()->json($game, 201);
    }
}