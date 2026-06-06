<?php

namespace App\Http\Controllers;

use App\Helpers\LevelSystem;
use App\Models\ActivityLog;
use App\Models\Challenge;
use App\Models\ChallengeSubmission;
use App\Models\Game;
use App\Models\ShopItem;
use App\Models\UserShopItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminChallengeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/challenges', [
            'challenges' => Challenge::query()
                ->with([
                    'item:id,name',
                    'game:id,title,cover_url,igdb_cover_url,steam_app_id,igdb_id',
                ])
                ->latest()
                ->get(),

            'shopItems' => ShopItem::query()
                ->where('is_active', true)
                ->get(['id', 'name', 'type', 'image_path']),

            'submissions' => $this->submissionsPayload(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'game_id' => ['required', 'integer'],
            'game_title' => ['required', 'string', 'max:255'],
            'game_cover_url' => ['nullable', 'string', 'max:2048'],
            'reward_xp' => ['required', 'integer', 'min:0'],
            'reward_coins' => ['required', 'integer', 'min:0'],
            'shop_item_id' => ['nullable', 'exists:shop_items,id'],
            'is_active' => ['boolean'],
        ]);

        $game = Game::query()
            ->where('id', $data['game_id'])
            ->orWhere('steam_app_id', $data['game_id'])
            ->orWhere('igdb_id', $data['game_id'])
            ->first();

        if (! $game) {
            $game = Game::query()->create([
                'title' => $data['game_title'],
                'slug' => $this->uniqueGameSlug($data['game_title']),
                'cover_url' => $data['game_cover_url'] ?? null,
                'steam_app_id' => $data['game_id'],
                'source' => 'steam',
            ]);
        }

        $data['game_id'] = $game->id;
        $data['game_name'] = $game->title;

        unset($data['game_title'], $data['game_cover_url']);

        Challenge::query()->create($data);

        return back();
    }

    public function approve(Request $request, ChallengeSubmission $submission): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($submission->status === 'approved') {
            return back();
        }

        $submission->load([
            'challenge:id,title,reward_xp,reward_coins,shop_item_id',
            'user:id,xp,coins,level',
        ]);

        $user = $submission->user;
        $challenge = $submission->challenge;

        DB::transaction(function () use ($submission, $user, $challenge, $data) {
            $newXp = $user->xp + $challenge->reward_xp;

            $user->update([
                'xp' => $newXp,
                'coins' => $user->coins + $challenge->reward_coins,
                'level' => LevelSystem::levelFromXp($newXp),
            ]);

            if ($challenge->shop_item_id) {
                UserShopItem::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'shop_item_id' => $challenge->shop_item_id,
                ], [
                    'is_equipped' => false,
                ]);
            }

            $user->challenges()->syncWithoutDetaching([
                $challenge->id => [
                    'completed_at' => now(),
                ],
            ]);

            $submission->update([
                'status' => 'approved',
                'admin_note' => $data['admin_note'] ?? null,
                'reviewed_at' => now(),
            ]);

            ActivityLog::query()->create([
                'user_id' => $user->id,
                'type' => 'challenge_approved',
                'message' => "Challenge approved: {$challenge->title}",
                'metadata' => [
                    'challenge_id' => $challenge->id,
                    'reward_xp' => $challenge->reward_xp,
                    'reward_coins' => $challenge->reward_coins,
                    'shop_item_id' => $challenge->shop_item_id,
                    'admin_note' => $data['admin_note'] ?? null,
                ],
            ]);
        });

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

    private function uniqueGameSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'game';
        $slug = $baseSlug;
        $counter = 2;

        while (Game::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function submissionsPayload()
    {
        return ChallengeSubmission::query()
            ->with([
                'challenge:id,title,game_id,game_name,reward_xp,reward_coins,shop_item_id',
                'challenge.game:id,title,cover_url,igdb_cover_url',
                'challenge.item:id,name',
                'user:id,name,email,steam_avatar_url',
            ])
            ->latest()
            ->limit(100)
            ->get([
                'id',
                'challenge_id',
                'user_id',
                'status',
                'admin_note',
                'description',
                'screenshot_path',
                'screenshot_paths',
                'created_at',
                'reviewed_at',
            ])
            ->map(fn (ChallengeSubmission $submission) => [
                'id' => $submission->id,
                'status' => $submission->status,
                'admin_note' => $submission->admin_note,
                'description' => $submission->description,
                'created_at' => $submission->created_at?->format('Y-m-d H:i'),
                'reviewed_at' => $submission->reviewed_at?->format('Y-m-d H:i'),

                'screenshot_url' => $submission->screenshot_path
                    ? Storage::url($submission->screenshot_path)
                    : null,

                'screenshot_urls' => collect($submission->screenshot_paths ?? [])
                    ->map(fn ($path) => Storage::url($path))
                    ->values(),

                'user' => [
                    'id' => $submission->user?->id,
                    'name' => $submission->user?->name,
                    'email' => $submission->user?->email,
                    'avatar' => $submission->user?->steam_avatar_url,
                ],

                'challenge' => [
                    'id' => $submission->challenge?->id,
                    'title' => $submission->challenge?->title,
                    'game_name' => $submission->challenge?->game?->title
                        ?? $submission->challenge?->game_name,
                    'reward_xp' => $submission->challenge?->reward_xp,
                    'reward_coins' => $submission->challenge?->reward_coins,

                    'item' => $submission->challenge?->item ? [
                        'id' => $submission->challenge->item->id,
                        'name' => $submission->challenge->item->name,
                    ] : null,
                ],
            ]);
    }
}