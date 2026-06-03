<?php

namespace App\Helpers;

use App\Http\Requests\StoreCustomGameRequest;
use App\Http\Requests\StoreCustomLabelRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use App\Http\Requests\UpdateGameMetaRequest;
use App\Models\PublicReview;
use App\Models\User;
use App\Models\UserGameMeta;
use App\Models\UserShopItem;
use App\Services\GameDetailsService;
use App\Services\GameLibraryService;
use App\Services\GameMetaService;
use App\Services\StatusService;
use App\Services\SteamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PayloadHelper
{
    public static function pageData(SteamService $steam): array
    {
        return [
            'user' => self::currentUser(),
            'games' => self::library()->allGames($steam),
            'statuses' => self::statuses(),
        ];
    }

    public static function publicProfilePageData(
        User $user,
        SteamService $steam
    ): array {
        $games = collect(
            self::library()->allGamesForUser($user, $steam)
        )->keyBy(fn ($game) => (string) $game['id']);

        return [
            'profileUser' => [
                'name' => $user->name,
                'steam_id' => $user->steam_id,
                'avatar' => $user->steam_avatar_url,
                'banner_url' => $user->banner_url,
            ],

            'featuredGames' => self::featuredGames($user, $games),
            'featuredReviews' => self::featuredReviews($user),
            'featuredWardrobeItems' => self::featuredWardrobeItems($user),
        ];
    }

    public static function backlogPageData(SteamService $steam): array
    {
        return [
            ...self::pageData($steam),
            'games' => self::library()->gamesByStatus($steam, 'Backlog'),
        ];
    }

    public static function playingPageData(SteamService $steam): array
    {
        return [
            ...self::pageData($steam),
            'games' => self::library()->gamesByStatus($steam, 'Playing'),
        ];
    }

    public static function finishedPageData(SteamService $steam): array
    {
        return [
            ...self::pageData($steam),
            'games' => self::library()->gamesByStatus($steam, 'Finished'),
        ];
    }

    public static function droppedPageData(SteamService $steam): array
    {
        return [
            ...self::pageData($steam),
            'games' => self::library()->gamesByStatus($steam, 'Dropped'),
        ];
    }

    public static function wishlistPageData(SteamService $steam): array
    {
        return [
            ...self::pageData($steam),
            'games' => self::library()->wishlistGames($steam),
        ];
    }

    public static function profilePageData(SteamService $steam): array
    {
        return [
            'user' => self::currentUser(),
            'games' => self::library()->allGames($steam),
            'activity' => self::library()->activityLog($steam),
            'equippedItems' => self::equippedItems(),
        ];
    }

    public static function gamePageData(
        string $gameId,
        SteamService $steam
    ): array {
        return [
            'user' => self::currentUser(),
            'statuses' => self::statuses(),
            'game' => self::details()->gameDetails($gameId, $steam),
        ];
    }

    public static function currentUser(): array
    {
        $user = Auth::user();
        $level = LevelSystem::levelFromXp($user->xp ?? 0);

        return [
            'name' => $user->name,
            'steam_id' => $user->steam_id,
            'avatar' => $user->steam_avatar_url,
            'banner_url' => $user->banner_url,
            'is_admin' => $user->is_admin,
            'xp' => $user->xp ?? 0,
            'coins' => $user->coins ?? 0,
            'level' => $level,
            'xp_for_current_level' => LevelSystem::xpForNextLevel($level - 1),
            'xp_for_next_level' => LevelSystem::xpForNextLevel($level),
        ];
    }

    public static function equippedItems(): array
    {
        return UserShopItem::query()
            ->with('item')
            ->where('user_id', Auth::id())
            ->where('is_equipped', true)
            ->get()
            ->map(fn ($ownedItem) => [
                'id' => $ownedItem->item->id,
                'name' => $ownedItem->item->name,
                'type' => $ownedItem->item->type,
                'metadata' => $ownedItem->item->metadata ?? [],
                'image_url' => $ownedItem->item->image_path
                    ? Storage::url($ownedItem->item->image_path)
                    : null,
            ])
            ->values()
            ->toArray();
    }

    public static function statuses(): array
    {
        return self::status()->statuses();
    }

    public static function customLabels(): array
    {
        return self::status()->customLabels();
    }

    public static function storeCustomLabel(
        StoreCustomLabelRequest $request
    ): RedirectResponse {
        return self::status()->storeCustomLabel($request);
    }

    public static function storeStatus(
        StoreCustomStatusRequest $request
    ): RedirectResponse {
        return self::status()->storeStatus($request);
    }

    public static function storeCustomGame(
        StoreCustomGameRequest $request
    ): RedirectResponse {
        return self::library()->storeCustomGame($request);
    }

    public static function storeMeta(
        UpdateGameMetaRequest $request,
        string $gameId
    ): RedirectResponse {
        return self::meta()->storeMeta($request, $gameId);
    }

    public static function steamSearch(
        SteamService $steam
    ): JsonResponse {
        $query = request('q');

        return response()->json(
            $query
                ? $steam->searchStore($query)
                : []
        );
    }

    private static function featuredGames(
        User $user,
        $games
    ): array {
        return UserGameMeta::query()
            ->where('user_id', $user->id)
            ->where('show_on_public_profile', true)
            ->latest('updated_at')
            ->get()
            ->map(function ($meta) use ($games) {
                $game = $games->get((string) $meta->game_id);

                return [
                    'id' => $meta->game_id,
                    'title' => $game['title']
                        ?? $game['name']
                        ?? (string) $meta->game_id,
                    'cover_url' => $game['cover_url'] ?? null,
                    'status' => $meta->status,
                    'note' => $meta->note,
                    'rating' => $meta->rating,
                    'recommended' => $meta->recommended,
                    'not_recommended' => $meta->not_recommended,
                    'updated_at' => $meta->updated_at?->diffForHumans(),
                ];
            })
            ->values()
            ->toArray();
    }

    private static function featuredReviews(User $user): array
    {
        return PublicReview::query()
            ->where('user_id', $user->id)
            ->where('is_featured_on_profile', true)
            ->latest('updated_at')
            ->get()
            ->map(fn ($review) => [
                'id' => $review->id,
                'title' => $review->title,
                'body' => $review->body,
                'rating' => $review->rating,
                'recommended' => $review->recommended,
                'not_recommended' => $review->not_recommended,
                'game_title' => $review->game_title,
                'created_at' => $review->created_at?->diffForHumans(),
            ])
            ->values()
            ->toArray();
    }

    private static function featuredWardrobeItems(User $user): array
    {
        return UserShopItem::query()
            ->with('item')
            ->where('user_id', $user->id)
            ->where('is_featured_on_profile', true)
            ->latest('updated_at')
            ->get()
            ->map(fn ($ownedItem) => [
                'id' => $ownedItem->item->id,
                'name' => $ownedItem->item->name,
                'description' => $ownedItem->item->description,
                'type' => $ownedItem->item->type,
                'image_url' => $ownedItem->item->image_path
                    ? Storage::url($ownedItem->item->image_path)
                    : null,
            ])
            ->values()
            ->toArray();
    }

    private static function library(): GameLibraryService
    {
        return app(GameLibraryService::class);
    }

    private static function details(): GameDetailsService
    {
        return app(GameDetailsService::class);
    }

    private static function meta(): GameMetaService
    {
        return app(GameMetaService::class);
    }

    private static function status(): StatusService
    {
        return app(StatusService::class);
    }
}