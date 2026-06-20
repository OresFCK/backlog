<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper;
use App\Models\AnticipatedGame;
use App\Models\Game;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PremiereController extends Controller
{
    public function index(Request $request)
    {
        $anticipatedIds = AnticipatedGame::query()
            ->where('user_id', $request->user()->id)
            ->pluck('game_id')
            ->all();

        $games = Game::query()
            ->whereNotNull('release_date')
            ->whereNotNull('igdb_cover_url')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereDate('release_date', '>=', today())
            ->whereDate('release_date', '<=', now()->endOfYear())
            ->orderBy('release_date')
            ->get([
                'id',
                'title',
                'slug',
                'release_date',
                'igdb_cover_url',
            ])
            ->map(fn (Game $game) => [
                'id' => $game->id,
                'title' => $game->title,
                'slug' => $game->slug,
                'release_date' => $game->release_date,
                'igdb_cover_url' => $game->igdb_cover_url,
                'is_anticipated' => in_array(
                    $game->id,
                    $anticipatedIds,
                    true
                ),
            ])
            ->values();

        return Inertia::render('premieres/index', [
            'user' => PayloadHelper::currentUser(),
            'games' => $games,
        ]);
    }
}