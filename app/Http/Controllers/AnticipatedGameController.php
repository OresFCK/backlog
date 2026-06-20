<?php

namespace App\Http\Controllers;

use App\Models\AnticipatedGame;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnticipatedGameController extends Controller
{
    public function toggle(Request $request, Game $game)
    {
        $anticipated = AnticipatedGame::query()
            ->where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
            ->first();

        if ($anticipated) {
            $anticipated->delete();
        } else {
            AnticipatedGame::query()->create([
                'user_id' => $request->user()->id,
                'game_id' => $game->id,
            ]);
        }

        Cache::forget('premieres:' . $request->user()->id . ':' . now()->year);
        Cache::forget('premieres:anticipated:v1:' . $request->user()->id . ':' . now()->year);
        Cache::forget('premieres:month:v1:' . $request->user()->id . ':' . now()->format('Y-m'));

        return back();
    }
}