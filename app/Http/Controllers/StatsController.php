<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\Challenge;
use App\Models\ChallengeSubmission;
use App\Models\UserShopItem;
use App\Services\SteamService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StatsController extends Controller
{
    public function index(SteamService $steam): Response
    {
        $user = Auth::user();
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
                'games' => [
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
                    'perfected_games' => $this->perfectedGames($games),
                ],

                'challenges' => $this->challengeStats($user),
                'wardrobe' => $this->wardrobeStats($user),
            ],
        ]);
    }

    private function statusBreakdown(Collection $games): array
    {
        return $games
            ->groupBy(function ($game) {
                if ($this->hasPerfectAchievements($game)) {
                    return 'Finished';
                }

                return $game['status'] ?? 'Backlog';
            })
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
            ->groupBy(function ($game) {
                if ($game['is_custom'] ?? false) {
                    $platform = trim((string) ($game['platform'] ?? ''));

                    return $platform !== ''
                        ? $platform
                        : 'Custom';
                }

                return 'Steam';
            })
            ->map(fn ($items, $platform) => [
                'platform' => $platform,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
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
                'achievement_percent' => $this->achievementPercent($game),
            ])
            ->values()
            ->toArray();
    }

    private function completionSummary(Collection $games): array
    {
        $total = max($games->count(), 1);

        $finished = $games->filter(fn ($game) =>
            strtolower($game['status'] ?? '') === 'finished'
            || $this->hasPerfectAchievements($game)
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

    private function perfectedGames(Collection $games): array
    {
        return $games
            ->filter(fn ($game) => $this->hasPerfectAchievements($game))
            ->map(fn ($game) => [
                'id' => $game['id'],
                'title' => $game['title'] ?? $game['name'] ?? 'Unknown game',
                'cover_url' => $game['cover_url'] ?? null,
                'achievement_percent' => $this->achievementPercent($game),
            ])
            ->values()
            ->toArray();
    }

    private function challengeStats($user): array
    {
        $joinedChallenges = $user
            ->challenges()
            ->get();

        $submissions = ChallengeSubmission::query()
            ->with('challenge:id,reward_xp,reward_coins,shop_item_id')
            ->where('user_id', $user->id)
            ->get();

        $approved = $submissions->where('status', 'approved');

        $joined = $joinedChallenges->count();

        $completed = $joinedChallenges
            ->filter(fn ($challenge) => (bool) $challenge->pivot?->completed_at)
            ->count();

        return [
            'available' => Challenge::query()
                ->where('is_active', true)
                ->count(),

            'joined' => $joined,
            'completed' => $completed,

            'pending' => $submissions
                ->where('status', 'pending')
                ->count(),

            'approved' => $approved->count(),

            'rejected' => $submissions
                ->where('status', 'rejected')
                ->count(),

            'completion_percent' => $joined
                ? round(($completed / $joined) * 100)
                : 0,

            'earned_xp' => $approved->sum(fn ($submission) =>
                (int) ($submission->challenge?->reward_xp ?? 0)
            ),

            'earned_coins' => $approved->sum(fn ($submission) =>
                (int) ($submission->challenge?->reward_coins ?? 0)
            ),

            'earned_items' => $approved
                ->filter(fn ($submission) => (bool) $submission->challenge?->shop_item_id)
                ->count(),
        ];
    }

    private function wardrobeStats($user): array
    {
        $items = UserShopItem::query()
            ->with('item:id,name,type,price')
            ->where('user_id', $user->id)
            ->get();

        return [
            'owned_items' => $items->count(),

            'equipped_items' => $items
                ->where('is_equipped', true)
                ->count(),

            'featured_items' => $items
                ->where('is_featured_on_profile', true)
                ->count(),

            'collection_value' => $items->sum(fn ($ownedItem) =>
                (int) ($ownedItem->item?->price ?? 0)
            ),

            'type_breakdown' => $items
                ->groupBy(fn ($ownedItem) => $ownedItem->item?->type ?? 'Unknown')
                ->map(fn ($group, $type) => [
                    'type' => $type,
                    'count' => $group->count(),
                ])
                ->values()
                ->toArray(),

            'featured' => $items
                ->where('is_featured_on_profile', true)
                ->map(fn ($ownedItem) => [
                    'id' => $ownedItem->item?->id,
                    'name' => $ownedItem->item?->name,
                    'type' => $ownedItem->item?->type,
                ])
                ->values()
                ->toArray(),
        ];
    }

    private function hasPerfectAchievements(array $game): bool
    {
        return $this->achievementPercent($game) >= 100;
    }

    private function achievementPercent(array $game): int
    {
        foreach ([
            'achievement_percent',
            'achievements_percent',
            'achievement_percentage',
            'achievements_percentage',
            'completion_percent',
            'steam_achievement_percent',
        ] as $key) {
            if (isset($game[$key])) {
                return (int) round((float) $game[$key]);
            }
        }

        $unlocked = $game['achievements_unlocked'] ?? $game['unlocked_achievements'] ?? null;
        $total = $game['achievements_total'] ?? $game['total_achievements'] ?? null;

        if ($total && $unlocked !== null) {
            return (int) round(((int) $unlocked / max((int) $total, 1)) * 100);
        }

        return 0;
    }
}