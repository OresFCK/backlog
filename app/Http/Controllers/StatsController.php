<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Services\SteamService;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class StatsController extends Controller
{
    public function index(SteamService $steam): Response
    {
        $pageData = Payload::pageData($steam);

        $games = collect($pageData['games'] ?? []);

        $totalGames = $games->count();

        $totalPlaytimeMinutes = $games->sum(fn ($game) =>
            (int) ($game['playtime_forever'] ?? 0)
        );

        $playedGames = $games->filter(fn ($game) =>
            (int) ($game['playtime_forever'] ?? 0) > 0
        );

        return Inertia::render('stats/index', [
            'user' => Payload::currentUser(),

            'stats' => [
                'total_games' => $totalGames,
                'played_games' => $playedGames->count(),
                'total_playtime_hours' => round($totalPlaytimeMinutes / 60, 1),
                'average_playtime_hours' => $playedGames->count()
                    ? round(($totalPlaytimeMinutes / 60) / $playedGames->count(), 1)
                    : 0,

                'status_breakdown' => $this->statusBreakdown($games),
                'platform_breakdown' => $this->platformBreakdown($games),
                'top_playtime_games' => $this->topPlaytimeGames($games),
                'completion_summary' => $this->completionSummary($games),
            ],
        ]);
    }

    private function statusBreakdown(Collection $games): array
    {
        return $games
            ->groupBy(fn ($game) => $game['status'] ?? 'Backlog')
            ->map(fn ($items, $status) => [
                'status' => $status,
                'count' => $items->count(),
            ])
            ->values()
            ->toArray();
    }

    private function platformBreakdown(Collection $games): array
    {
        return $games
            ->groupBy(fn ($game) => ($game['is_custom'] ?? false) ? 'Custom' : 'Steam')
            ->map(fn ($items, $platform) => [
                'platform' => $platform,
                'count' => $items->count(),
            ])
            ->values()
            ->toArray();
    }

    private function topPlaytimeGames(Collection $games): array
    {
        return $games
            ->sortByDesc(fn ($game) => (int) ($game['playtime_forever'] ?? 0))
            ->take(8)
            ->map(fn ($game) => [
                'id' => $game['id'],
                'title' => $game['title'] ?? $game['name'] ?? 'Unknown game',
                'cover_url' => $game['cover_url'] ?? null,
                'playtime_hours' => round(((int) ($game['playtime_forever'] ?? 0)) / 60, 1),
                'platform' => ($game['is_custom'] ?? false) ? 'Custom' : 'Steam',
            ])
            ->values()
            ->toArray();
    }

    private function completionSummary(Collection $games): array
    {
        $total = max($games->count(), 1);

        $finished = $games->filter(fn ($game) =>
            strtolower($game['status'] ?? '') === 'finished'
        )->count();

        $dropped = $games->filter(fn ($game) =>
            strtolower($game['status'] ?? '') === 'dropped'
        )->count();

        $playing = $games->filter(fn ($game) =>
            strtolower($game['status'] ?? '') === 'playing'
        )->count();

        return [
            'finished' => $finished,
            'dropped' => $dropped,
            'playing' => $playing,
            'finished_percent' => round(($finished / $total) * 100),
            'dropped_percent' => round(($dropped / $total) * 100),
            'playing_percent' => round(($playing / $total) * 100),
        ];
    }
}