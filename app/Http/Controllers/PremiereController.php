<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper;
use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class PremiereController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $year = now()->year;

        $months = Cache::remember(
            "premieres:months:v2:{$year}",
            now()->addHours(6),
            function () {
                return Game::query()
                    ->whereNotNull('release_date')
                    ->whereNotNull('igdb_cover_url')
                    ->whereNotNull('slug')
                    ->where('slug', '!=', '')
                    ->whereDate('release_date', '>=', today())
                    ->whereDate('release_date', '<=', now()->endOfYear())
                    ->orderBy('release_date')
                    ->get(['release_date'])
                    ->groupBy(fn (Game $game) => $game->release_date->format('Y-m'))
                    ->map(fn ($games, string $month) => [
                        'month' => $month,
                        'label' => Carbon::createFromFormat('Y-m', $month)->format('F Y'),
                        'total' => $games->count(),
                    ])
                    ->values()
                    ->all();
            }
        );

        return Inertia::render('premieres/index', [
            'user' => PayloadHelper::currentUser(),
            'months' => $months,
            'anticipatedGames' => $this->anticipatedGames($userId, $year),
        ]);
    }

    public function month(Request $request, string $month)
    {
        abort_unless(preg_match('/^\d{4}-\d{2}$/', $month), 404);

        $userId = $request->user()->id;
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $games = Cache::remember(
            "premieres:month:v2:{$userId}:{$month}",
            now()->addHours(6),
            function () use ($userId, $start, $end) {
                return Game::query()
                    ->select([
                        'games.id',
                        'games.title',
                        'games.slug',
                        'games.release_date',
                        'games.igdb_cover_url',
                    ])
                    ->selectRaw(
                        'exists (
                            select 1
                            from anticipated_games
                            where anticipated_games.game_id = games.id
                            and anticipated_games.user_id = ?
                        ) as is_anticipated',
                        [$userId]
                    )
                    ->whereNotNull('release_date')
                    ->whereNotNull('igdb_cover_url')
                    ->whereNotNull('slug')
                    ->where('slug', '!=', '')
                    ->whereBetween('release_date', [$start, $end])
                    ->orderBy('release_date')
                    ->get()
                    ->map(fn (Game $game) => $this->gamePayload($game))
                    ->values()
                    ->all();
            }
        );

        return response()->json([
            'games' => $games,
        ]);
    }

    private function anticipatedGames(int $userId, int $year): array
    {
        return Cache::remember(
            "premieres:anticipated:v2:{$userId}:{$year}",
            now()->addHours(6),
            function () use ($userId) {
                return Game::query()
                    ->select([
                        'games.id',
                        'games.title',
                        'games.slug',
                        'games.release_date',
                        'games.igdb_cover_url',
                    ])
                    ->join('anticipated_games', 'anticipated_games.game_id', '=', 'games.id')
                    ->where('anticipated_games.user_id', $userId)
                    ->whereNotNull('games.release_date')
                    ->whereNotNull('games.igdb_cover_url')
                    ->whereNotNull('games.slug')
                    ->where('games.slug', '!=', '')
                    ->whereDate('games.release_date', '>=', today())
                    ->whereDate('games.release_date', '<=', now()->endOfYear())
                    ->orderBy('games.release_date')
                    ->get()
                    ->map(fn (Game $game) => [
                        ...$this->gamePayload($game),
                        'is_anticipated' => true,
                    ])
                    ->values()
                    ->all();
            }
        );
    }

    private function gamePayload(Game $game): array
    {
        return [
            'id' => $game->id,
            'title' => $game->title,
            'slug' => $game->slug,
            'release_date' => $game->release_date,
            'month_label' => $game->release_date->format('F Y'),
            'formatted_release_date' => $game->release_date->format('d M Y'),
            'igdb_cover_url' => $game->igdb_cover_url,
            'is_anticipated' => (bool) ($game->is_anticipated ?? false),
        ];
    }
}