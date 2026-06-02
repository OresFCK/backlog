<?php

namespace App\Http\Controllers;

use App\Helpers\LevelSystem;
use App\Models\ActivityLog;
use App\Models\Challenge;
use App\Models\ChallengeSubmission;
use App\Models\ShopItem;
use App\Models\UserShopItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminChallengeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/challenges', [
            'challenges' => Challenge::with('item')->latest()->get(),
            'shopItems' => ShopItem::where('is_active', true)->get(),
            'submissions' => $this->submissionsPayload(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Challenge::create($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'game_name' => ['required', 'string', 'max:255'],
            'reward_xp' => ['required', 'integer', 'min:0'],
            'reward_coins' => ['required', 'integer', 'min:0'],
            'shop_item_id' => ['nullable', 'exists:shop_items,id'],
            'is_active' => ['boolean'],
        ]));

        return back();
    }

    public function approve(ChallengeSubmission $submission): RedirectResponse
    {
        if ($submission->status === 'approved') {
            return back();
        }

        $submission->load(['challenge', 'user']);

        $user = $submission->user;
        $challenge = $submission->challenge;

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

        $user->challenges()->syncWithoutDetaching([
            $challenge->id,
        ]);

        $user->challenges()->updateExistingPivot($challenge->id, [
            'completed_at' => now(),
        ]);

        $submission->update([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);

        if (class_exists(ActivityLog::class)) {
            ActivityLog::create([
                'user_id' => $user->id,
                'type' => 'challenge_approved',
                'message' => "Challenge approved: {$challenge->title}",
                'metadata' => [
                    'challenge_id' => $challenge->id,
                    'reward_xp' => $challenge->reward_xp,
                    'reward_coins' => $challenge->reward_coins,
                    'shop_item_id' => $challenge->shop_item_id,
                ],
            ]);
        }

        return back();
    }

    public function reject(Request $request, ChallengeSubmission $submission): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $submission->update([
            'status' => 'rejected',
            'admin_note' => $data['admin_note'] ?? null,
            'reviewed_at' => now(),
        ]);

        return back();
    }

    public function destroy(Challenge $challenge): RedirectResponse
    {
        $challenge->delete();

        return back();
    }

    private function submissionsPayload()
    {
        return ChallengeSubmission::query()
            ->with(['challenge.item', 'user'])
            ->latest()
            ->get()
            ->map(fn (ChallengeSubmission $submission) => [
                'id' => $submission->id,
                'status' => $submission->status,
                'admin_note' => $submission->admin_note,
                'created_at' => $submission->created_at?->format('Y-m-d H:i'),
                'reviewed_at' => $submission->reviewed_at?->format('Y-m-d H:i'),

                'screenshot_url' => Storage::url($submission->screenshot_path),

                'user' => [
                    'id' => $submission->user->id,
                    'name' => $submission->user->name,
                    'email' => $submission->user->email,
                    'avatar' => $submission->user->steam_avatar_url,
                ],

                'challenge' => [
                    'id' => $submission->challenge->id,
                    'title' => $submission->challenge->title,
                    'game_name' => $submission->challenge->game_name,
                    'reward_xp' => $submission->challenge->reward_xp,
                    'reward_coins' => $submission->challenge->reward_coins,

                    'item' => $submission->challenge->item ? [
                        'id' => $submission->challenge->item->id,
                        'name' => $submission->challenge->item->name,
                    ] : null,
                ],
            ]);
    }
}