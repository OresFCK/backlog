<?php

namespace App\Http\Controllers;

use App\Models\UserGame;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function now(Request $request)
    {
        $userGames = UserGame::query()
            ->with(['game', 'platform'])
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['backlog', 'playing'])
            ->get();

        $recommendations = $userGames
            ->map(function (UserGame $userGame) {
                $game = $userGame->game;

                $score = 0;

                if ($game->average_playtime_minutes !== null) {
                    if ($game->average_playtime_minutes <= 300) {
                        $score += 30;
                    } elseif ($game->average_playtime_minutes <= 900) {
                        $score += 20;
                    } else {
                        $score += 10;
                    }
                }

                if ($game->steam_rating_percent !== null) {
                    $score += (int) round($game->steam_rating_percent / 5);
                }

                if ($userGame->priority === 'high') {
                    $score += 30;
                }

                if ($userGame->priority === 'medium') {
                    $score += 15;
                }

                if ($userGame->status === 'playing') {
                    $score += 20;
                }

                if ($userGame->last_played_at === null) {
                    $score += 10;
                }

                return [
                    'user_game' => $userGame,
                    'score' => $score,
                    'reason' => $this->buildReason($userGame),
                ];
            })
            ->sortByDesc('score')
            ->take(3)
            ->values();

        return response()->json($recommendations);
    }

    private function buildReason(UserGame $userGame): string
    {
        $reasons = [];

        if ($userGame->status === 'playing') {
            $reasons[] = 'już zacząłeś tę grę';
        }

        if ($userGame->priority === 'high') {
            $reasons[] = 'ma wysoki priorytet';
        }

        if ($userGame->game->average_playtime_minutes !== null && $userGame->game->average_playtime_minutes <= 600) {
            $reasons[] = 'jest relatywnie krótka';
        }

        if ($userGame->game->steam_rating_percent !== null && $userGame->game->steam_rating_percent >= 85) {
            $reasons[] = 'ma bardzo dobre oceny';
        }

        if (empty($reasons)) {
            return 'pasuje jako następna gra z Twojego backlogu';
        }

        return 'Polecane, bo ' . implode(', ', $reasons) . '.';
    }
}