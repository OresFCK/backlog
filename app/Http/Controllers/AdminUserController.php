<?php

namespace App\Http\Controllers;

use App\Helpers\LevelSystem;
use App\Models\ActivityLog;
use App\Models\Challenge;
use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserShopItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->string('q')->toString();

        if (! $query) {
            return response()->json([]);
        }

        return response()->json(
            User::query()
                ->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('steam_id', 'like', "%{$query}%")
                ->limit(10)
                ->get()
                ->map(fn (User $user) => $this->userPayload($user))
        );
    }

    public function logs(User $user): JsonResponse
    {
        return response()->json(
            $user->activityLogs()
                ->where('type', 'not like', 'admin_%')
                ->latest()
                ->limit(30)
                ->get()
                ->map(function (ActivityLog $log) {
                    $reviewId =
                        $log->metadata['public_review_id']
                        ?? $log->metadata['review_id']
                        ?? null;

                    return [
                        'id' => $log->id,
                        'type' => $log->type,
                        'message' => $log->message,
                        'metadata' => $log->metadata,
                        'created_at' => $log->created_at?->diffForHumans(),

                        'url' => $reviewId
                            ? "/admin/reviews/{$reviewId}"
                            : null,
                    ];
                })
        );
    }

    public function grantables(): JsonResponse
    {
        return response()->json([
            'shopItems' => ShopItem::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'type']),

            'challenges' => Challenge::query()
                ->where('is_active', true)
                ->orderBy('title')
                ->get([
                    'id',
                    'title',
                    'game_name',
                    'reward_xp',
                    'reward_coins',
                ]),
        ]);
    }

    public function availableChallenges(User $user): JsonResponse
    {
        $completedChallengeIds = $user->challenges()
            ->whereNotNull('completed_at')
            ->pluck('challenges.id');

        return response()->json(
            Challenge::query()
                ->where('is_active', true)
                ->whereNotIn('id', $completedChallengeIds)
                ->orderBy('title')
                ->get([
                    'id',
                    'title',
                    'game_name',
                    'reward_xp',
                    'reward_coins',
                ])
        );
    }

    public function addCoins(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $user->increment('coins', $data['amount']);

        $this->log(
            $user,
            'admin_coins_added',
            "Admin added {$data['amount']} coins.",
            $data
        );

        return back();
    }

    public function addXp(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $newXp = ($user->xp ?? 0) + $data['amount'];

        $user->update([
            'xp' => $newXp,
            'level' => LevelSystem::levelFromXp($newXp),
        ]);

        $this->log(
            $user,
            'admin_xp_added',
            "Admin added {$data['amount']} XP.",
            $data
        );

        return back();
    }

    public function setLevel(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'level' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'level' => $data['level'],
        ]);

        $this->log(
            $user,
            'admin_level_set',
            "Admin set level to {$data['level']}.",
            $data
        );

        return back();
    }

    public function grantItem(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'shop_item_id' => ['required', 'exists:shop_items,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $item = ShopItem::query()->findOrFail($data['shop_item_id']);

        UserShopItem::query()->firstOrCreate([
            'user_id' => $user->id,
            'shop_item_id' => $item->id,
        ], [
            'is_equipped' => false,
            'is_featured_on_profile' => false,
        ]);

        $this->log(
            $user,
            'admin_item_granted',
            "Admin granted item: {$item->name}.",
            [
                'shop_item_id' => $item->id,
                'item_name' => $item->name,
                'reason' => $data['reason'] ?? null,
            ]
        );

        return back();
    }

    public function completeChallenge(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'challenge_id' => ['required', 'exists:challenges,id'],
            'grant_rewards' => ['boolean'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $challenge = Challenge::query()->findOrFail($data['challenge_id']);

        $alreadyCompleted = $user->challenges()
            ->where('challenge_id', $challenge->id)
            ->whereNotNull('completed_at')
            ->exists();

        if ($alreadyCompleted) {
            return back()->with(
                'error',
                'Challenge has already been completed by this user.'
            );
        }

        $user->challenges()->syncWithoutDetaching([
            $challenge->id => [
                'completed_at' => now(),
            ],
        ]);

        if ($data['grant_rewards'] ?? false) {
            $newXp = ($user->xp ?? 0) + $challenge->reward_xp;

            $user->update([
                'xp' => $newXp,
                'coins' => ($user->coins ?? 0) + $challenge->reward_coins,
                'level' => LevelSystem::levelFromXp($newXp),
            ]);

            if ($challenge->shop_item_id) {
                UserShopItem::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'shop_item_id' => $challenge->shop_item_id,
                ], [
                    'is_equipped' => false,
                    'is_featured_on_profile' => false,
                ]);
            }
        }

        $this->log(
            $user,
            'admin_challenge_completed',
            "Admin completed challenge: {$challenge->title}.",
            [
                'challenge_id' => $challenge->id,
                'grant_rewards' => $data['grant_rewards'] ?? false,
                'reason' => $data['reason'] ?? null,
            ]
        );

        return back();
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'steam_id' => $user->steam_id,
            'avatar' => $user->steam_avatar_url,
            'coins' => $user->coins ?? 0,
            'xp' => $user->xp ?? 0,
            'level' => $user->level ?? 1,
        ];
    }

    private function log(
        User $user,
        string $type,
        string $message,
        array $metadata = []
    ): void {
        ActivityLog::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'message' => $message,
            'metadata' => $metadata,
        ]);
    }
}