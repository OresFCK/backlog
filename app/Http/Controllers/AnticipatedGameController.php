<?php

namespace App\Http\Controllers;

use App\Models\AnticipatedGame;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnticipatedGameController extends Controller
{
    public function toggle(
        Request $request,
        string $gameIdentifier
    ): RedirectResponse {
        abort_unless(
            ctype_digit($gameIdentifier),
            404
        );

        $game = Game::query()
            ->whereKey((int) $gameIdentifier)
            ->orWhere(
                'igdb_id',
                (int) $gameIdentifier
            )
            ->first();

        if (! $game) {
            return back()->with(
                'error',
                'The selected game could not be found.'
            );
        }

        $userId = $request->user()->id;

        $anticipated = AnticipatedGame::query()
            ->where('user_id', $userId)
            ->where('game_id', $game->id)
            ->first();

        if ($anticipated) {
            $anticipated->delete();
        } else {
            AnticipatedGame::query()->create([
                'user_id' => $userId,
                'game_id' => $game->id,
            ]);
        }

        return back();
    }
}