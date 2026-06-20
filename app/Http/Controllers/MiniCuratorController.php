<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\CustomList;
use App\Models\PublicReview;
use App\Models\User;
use App\Models\UserConnection;
use App\Services\SteamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MiniCuratorController extends Controller
{
    public function index(SteamService $steam): Response
    {
        $currentUserId = auth()->id();
        $perTypeLimit = 8;
        $feedLimit = 12;

        $followingIds = UserConnection::query()
            ->where('sender_id', $currentUserId)
            ->where('type', 'follow')
            ->pluck('receiver_id');

        $curators = User::query()
            ->where('is_curator', true)
            ->where('id', '!=', $currentUserId)
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->visible_name,
                'steam_id' => $user->steam_id,
                'avatar' => $user->steam_avatar_url,
                'is_following' => $followingIds->contains($user->id),
            ])
            ->values();

        $reviews = PublicReview::query()
            ->with('user')
            ->whereIn('user_id', $followingIds)
            ->where('is_public', true)
            ->latest()
            ->limit($perTypeLimit)
            ->get()
            ->map(fn (PublicReview $review) => [
                'type' => 'review',
                'id' => 'review_' . $review->id,
                'created_at' => $review->created_at,
                'created_at_human' => $review->created_at?->diffForHumans(),
                'user' => [
                    'id' => $review->user->id,
                    'name' => $review->user->visible_name,
                    'steam_id' => $review->user->steam_id,
                    'avatar' => $review->user->steam_avatar_url,
                ],
                'title' => $review->title,
                'body' => $review->body,
                'game_title' => $review->game_title,
                'rating' => $review->rating,
                'recommended' => $review->recommended,
                'not_recommended' => $review->not_recommended,
                'screenshot_url' => $review->screenshot_path
                    ? Storage::url($review->screenshot_path)
                    : null,
            ]);

        $lists = CustomList::query()
            ->with(['user', 'items'])
            ->withCount('items')
            ->whereIn('user_id', $followingIds)
            ->where('visibility', 'public')
            ->latest()
            ->limit($perTypeLimit)
            ->get()
            ->map(fn (CustomList $list) => [
                'type' => 'list',
                'id' => 'list_' . $list->id,
                'created_at' => $list->created_at,
                'created_at_human' => $list->created_at?->diffForHumans(),
                'user' => [
                    'id' => $list->user->id,
                    'name' => $list->user->visible_name,
                    'steam_id' => $list->user->steam_id,
                    'avatar' => $list->user->steam_avatar_url,
                ],
                'title' => $list->title,
                'description' => $list->description,
                'visibility' => $list->visibility,
                'items_count' => $list->items_count,
                'items' => $list->items
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'game_id' => $item->game_id,
                        'title' => $item->game_title ?? 'Unknown game',
                        'cover_url' => $item->game_cover_url,
                        'slug' => $item->game_slug,
                        'steam_app_id' => $item->steam_app_id,
                        'position' => $item->position,
                        'note' => $item->note,
                    ])
                    ->values()
                    ->toArray(),
            ]);

        $feed = $reviews
            ->merge($lists)
            ->sortByDesc('created_at')
            ->take($feedLimit)
            ->values();

        return Inertia::render('mini-curators/index', [
            ...Payload::pageData($steam),
            'curators' => $curators,
            'feed' => $feed,
        ]);
    }

    public function follow(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 422);
        abort_unless($user->is_curator, 404);

        UserConnection::updateOrCreate(
            [
                'sender_id' => auth()->id(),
                'receiver_id' => $user->id,
                'type' => 'follow',
            ],
            [
                'status' => 'accepted',
            ]
        );

        return back();
    }

    public function unfollow(User $user): RedirectResponse
    {
        UserConnection::query()
            ->where('sender_id', auth()->id())
            ->where('receiver_id', $user->id)
            ->where('type', 'follow')
            ->delete();

        return back();
    }
}