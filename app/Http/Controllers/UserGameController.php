<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\UserGame;
use Illuminate\Http\Request;

class UserGameController extends Controller
{
    public function index(Request $request)
    {
        $query = UserGame::query()
            ->with(['game', 'platform'])
            ->where('user_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $userGames = $query
            ->latest()
            ->paginate(24);

        return response()->json($userGames);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'game_id' => ['nullable', 'exists:games,id'],
            'title' => ['required_without:game_id', 'string', 'max:255'],

            'platform_id' => ['nullable', 'exists:platforms,id'],

            'status' => ['nullable', 'in:backlog,playing,finished,dropped,wishlist'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'source' => ['nullable', 'in:steam_import,manual,wishlist'],

            'playtime_minutes' => ['nullable', 'integer', 'min:0'],
            'personal_rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (!empty($validated['game_id'])) {
            $game = Game::findOrFail($validated['game_id']);
        } else {
            $game = Game::firstOrCreate([
                'title' => $validated['title'],
            ]);
        }

        $userGame = UserGame::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'game_id' => $game->id,
                'platform_id' => $validated['platform_id'] ?? null,
            ],
            [
                'status' => $validated['status'] ?? 'backlog',
                'priority' => $validated['priority'] ?? null,
                'source' => $validated['source'] ?? 'manual',
                'playtime_minutes' => $validated['playtime_minutes'] ?? null,
                'personal_rating' => $validated['personal_rating'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'added_to_library_at' => now(),
            ]
        );

        return response()->json(
            $userGame->load(['game', 'platform']),
            201
        );
    }

    public function update(Request $request, UserGame $userGame)
    {
        abort_if($userGame->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'status' => ['nullable', 'in:backlog,playing,finished,dropped,wishlist'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'playtime_minutes' => ['nullable', 'integer', 'min:0'],
            'personal_rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'last_played_at' => ['nullable', 'date'],
        ]);

        if (($validated['status'] ?? null) === 'playing' && !$userGame->started_at) {
            $validated['started_at'] = now();
        }

        if (($validated['status'] ?? null) === 'finished' && !$userGame->finished_at) {
            $validated['finished_at'] = now();
        }

        $userGame->update($validated);

        return response()->json(
            $userGame->load(['game', 'platform'])
        );
    }

    public function destroy(Request $request, UserGame $userGame)
    {
        abort_if($userGame->user_id !== $request->user()->id, 403);

        $userGame->delete();

        return response()->json([
            'message' => 'Game removed from your backlog.',
        ]);
    }
}