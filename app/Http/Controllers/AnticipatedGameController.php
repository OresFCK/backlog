<?php

namespace App\Http\Controllers;

use App\Models\AnticipatedGame;
use App\Models\Game;
use Illuminate\Http\Request;

class AnticipatedGameController extends Controller
{
    public function toggle(Request $request, int $gameId)
    {
        $game = Game::query()->findOrFail($gameId);

        $anticipated = AnticipatedGame::query()
            ->where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->first();

        if ($anticipated) {
            $anticipated->delete();

            return back();
        }

        AnticipatedGame::create([
            'user_id' => $request->user()->id,
            'game_id' => $game->id,
        ]);

        return back();
    }
}