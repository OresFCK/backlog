<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\Challenge;
use App\Models\UserShopItem;
use App\Helpers\LevelSystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ChallengeController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $challenges = Challenge::query()
            ->with('item')
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(function (Challenge $challenge) use ($user) {
                $pivot = $user->challenges()
                    ->where('challenge_id', $challenge->id)
                    ->first()?->pivot;

                return [
                    'id' => $challenge->id,
                    'title' => $challenge->title,
                    'description' => $challenge->description,
                    'reward_xp' => $challenge->reward_xp,
                    'reward_coins' => $challenge->reward_coins,
                    'joined' => (bool) $pivot,
                    'completed' => (bool) $pivot?->completed_at,

                    'item' => $challenge->item ? [
                        'id' => $challenge->item->id,
                        'name' => $challenge->item->name,
                        'type' => $challenge->item->type,
                    ] : null,
                ];
            });

        return Inertia::render('challenges/index', [
            'user' => Payload::currentUser(),
            'challenges' => $challenges,
        ]);
    }

    public function join(Challenge $challenge): RedirectResponse
    {
        Auth::user()->challenges()->syncWithoutDetaching([
            $challenge->id,
        ]);

        return back();
    }

    public function complete(Challenge $challenge): RedirectResponse
    {
        $user = Auth::user();

        $joinedChallenge = $user->challenges()
            ->where('challenge_id', $challenge->id)
            ->first();

        if (! $joinedChallenge) {
            return back();
        }

        if ($joinedChallenge->pivot->completed_at) {
            return back();
        }

        $user->increment('xp', $challenge->reward_xp);
        $user->increment('coins', $challenge->reward_coins);

        $user->update([
            'level' => LevelSystem::levelFromXp($user->fresh()->xp),
        ]);

        if ($challenge->shop_item_id) {
            UserShopItem::firstOrCreate([
                'user_id' => $user->id,
                'shop_item_id' => $challenge->shop_item_id,
            ], [
                'is_equipped' => false,
            ]);
        }

        $user->challenges()->updateExistingPivot($challenge->id, [
            'completed_at' => now(),
        ]);

        return back();
    }
}