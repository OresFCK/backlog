<?php

namespace App\Helpers;

use App\Http\Requests\StoreCustomGameRequest;
use App\Http\Requests\StoreCustomLabelRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use App\Http\Requests\UpdateGameMetaRequest;
use App\Models\PublicReview;
use App\Models\PublicReviewReport;
use App\Models\User;
use App\Models\UserGameMeta;
use App\Models\UserShopItem;
use App\Services\GameDetailsService;
use App\Services\GameLibraryService;
use App\Services\GameMetaService;
use App\Services\StatusService;
use App\Services\SteamService;
use App\Helpers\CacheKeys;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PayloadHelper
{
    public static function pageData(SteamService $steam): array
    {
        $userId = Auth::id();

        return [
            ...self::basePageData(),
            'games' => Cache::remember(
                CacheKeys::userLibrary($userId),
                now()->addMinutes(30),
                fn () => self::library()->allGames($steam)
            ),
        ];
    }

    private static function basePageData(): array
    {
        $userId = Auth::id();

        return Cache::remember(
            CacheKeys::userBase($userId),
            now()->addMinutes(10),
            fn () => [
                'user' => self::currentUser(),
                'statuses' => self::statuses(),
            ]
        );
    }

    public static function publicProfilePageData(
        User $user,
        SteamService $steam
    ): array {
        return Cache::remember(
            CacheKeys::publicProfile($user->id),
            now()->addMinutes(30),
            function () use ($user, $steam) {
                $games = collect(
                    Cache::remember(
                        CacheKeys::userLibraryForUser($user->id),
                        now()->addMinutes(30),
                        fn () => self::library()->allGamesForUser($user, $steam)
                    )
                )->keyBy(fn ($game) => (string) $game['id']);

                return [
                    'profileUser' => [
                        'name' => $user->visible_name,
                        'display_name' => $user->display_name,
                        'steam_persona_name' => $user->steam_persona_name,
                        'steam_id' => $user->steam_id,
                        'avatar' => $user->steam_avatar_url,
                        'banner_url' => $user->banner_url,
                    ],

                    'featuredGames' => self::featuredGames($user, $games),
                    'featuredReviews' => self::featuredReviews($user),
                    'featuredWardrobeItems' => self::featuredWardrobeItems($user),
                ];
            }
        );
    }

    public static function backlogPageData(SteamService $steam): array
    {
        return self::statusPageData($steam, 'Backlog');
    }

    public static function playingPageData(SteamService $steam): array
    {
        return self::statusPageData($steam, 'Playing');
    }

    public static function finishedPageData(SteamService $steam): array
    {
        return self::statusPageData($steam, 'Finished');
    }

    public static function droppedPageData(SteamService $steam): array
    {
        return self::statusPageData($steam, 'Dropped');
    }

    private static function statusPageData(
        SteamService $steam,
        string $status
    ): array {
        $userId = Auth::id();

        return [
            ...self::basePageData(),
            'games' => Cache::remember(
                CacheKeys::userLibraryStatus($userId, $status),
                now()->addMinutes(30),
                fn () => self::library()->gamesByStatus($steam, $status)
            ),
        ];
    }

    public static function wishlistPageData(SteamService $steam): array
    {
        $userId = Auth::id();

        return [
            ...self::basePageData(),
            'games' => Cache::remember(
                CacheKeys::userWishlist($userId),
                now()->addMinutes(30),
                fn () => self::library()->wishlistGames($steam)
            ),
        ];
    }

    public static function profilePageData(SteamService $steam): array
    {
        $user = Auth::user();

        return Cache::remember(
            CacheKeys::profilePage($user->id),
            now()->addMinutes(15),
            function () use ($user, $steam) {
                $gamesForUser = collect(
                    Cache::remember(
                        CacheKeys::userLibraryForUser($user->id),
                        now()->addMinutes(30),
                        fn () => self::library()->allGamesForUser($user, $steam)
                    )
                )->keyBy(fn ($game) => (string) $game['id']);

                return [
                    'user' => self::currentUser(),

                    'games' => Cache::remember(
                        CacheKeys::userLibrary($user->id),
                        now()->addMinutes(30),
                        fn () => self::library()->allGames($steam)
                    ),

                    'activity' => Cache::remember(
                        CacheKeys::userActivity($user->id),
                        now()->addMinutes(15),
                        fn () => self::library()->activityLog($steam)
                    ),

                    'equippedItems' => self::equippedItems(),
                    'featuredGames' => self::featuredGames($user, $gamesForUser),
                    'featuredReviews' => self::featuredReviews($user),
                    'featuredWardrobeItems' => self::featuredWardrobeItems($user),
                ];
            }
        );
    }

    public static function gamePageData(
        string $gameId,
        SteamService $steam
    ): array {
        return [
            'user' => self::currentUser(),
            'statuses' => self::statuses(),

            'game' => Cache::remember(
                CacheKeys::gameDetails($gameId),
                now()->addDay(),
                fn () => self::details()->gameDetails($gameId, $steam)
            ),
        ];
    }

    public static function currentUser(): array
    {
        $user = Auth::user();
        $level = LevelSystem::levelFromXp($user->xp ?? 0);

        return [
            'name' => $user->visible_name,
            'display_name' => $user->display_name,
            'steam_persona_name' => $user->steam_persona_name,
            'steam_id' => $user->steam_id,
            'avatar' => $user->steam_avatar_url,
            'banner_url' => $user->banner_url,
            'is_admin' => $user->is_admin,
            'xp' => $user->xp ?? 0,
            'coins' => $user->coins ?? 0,
            'level' => $level,
            'xp_for_current_level' => LevelSystem::xpForNextLevel($level - 1),
            'xp_for_next_level' => LevelSystem::xpForNextLevel($level),
            'is_curator' => $user->is_curator,
        ];
    }

    public static function equippedItems(): array
    {
        $userId = Auth::id();

        return Cache::remember(
            CacheKeys::userEquippedItems($userId),
            now()->addMinutes(30),
            fn () => UserShopItem::query()
                ->with('item')
                ->where('user_id', $userId)
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
                ->toArray()
        );
    }

    public static function reviewReports(): array
    {
        return Cache::remember(
            CacheKeys::reviewReports(),
            now()->addMinutes(5),
            fn () => PublicReviewReport::query()
                ->with([
                    'review.user',
                    'reporter',
                ])
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn (PublicReviewReport $report) => [
                    'id' => $report->id,
                    'reason' => $report->reason,
                    'status' => $report->status,
                    'created_at' => $report->created_at?->diffForHumans(),

                    'reporter' => [
                        'id' => $report->reporter?->id,
                        'name' => $report->reporter?->visible_name,
                        'avatar' => $report->reporter?->steam_avatar_url,
                    ],

                    'review' => $report->review ? [
                        'id' => $report->review->id,
                        'title' => $report->review->title,
                        'body' => $report->review->body,
                        'game_title' => $report->review->game_title,
                        'rating' => $report->review->rating,
                        'recommended' => $report->review->recommended,
                        'not_recommended' => $report->review->not_recommended,

                        'user' => [
                            'id' => $report->review->user?->id,
                            'name' => $report->review->user?->visible_name,
                            'avatar' => $report->review->user?->steam_avatar_url,
                        ],
                    ] : null,
                ])
                ->values()
                ->toArray()
        );
    }

    public static function statuses(): array
    {
        return Cache::remember(
            CacheKeys::userStatuses(Auth::id()),
            now()->addHour(),
            fn () => self::status()->statuses()
        );
    }

    public static function customLabels(): array
    {
        return Cache::remember(
            CacheKeys::userCustomLabels(Auth::id()),
            now()->addHour(),
            fn () => self::status()->customLabels()
        );
    }

    public static function storeCustomLabel(
        StoreCustomLabelRequest $request
    ): RedirectResponse {
        $response = self::status()->storeCustomLabel($request);

        self::flushUserCache(Auth::id());

        return $response;
    }

    public static function storeStatus(
        StoreCustomStatusRequest $request
    ): RedirectResponse {
        $response = self::status()->storeStatus($request);

        self::flushUserCache(Auth::id());

        return $response;
    }

    public static function storeCustomGame(
        StoreCustomGameRequest $request
    ): RedirectResponse {
        $response = self::library()->storeCustomGame($request);

        self::flushUserCache(Auth::id());

        return $response;
    }

    public static function storeMeta(
        UpdateGameMetaRequest $request,
        string $gameId
    ): RedirectResponse {
        $response = self::meta()->storeMeta($request, $gameId);

        self::flushUserCache(Auth::id());
        Cache::forget(CacheKeys::gameDetails($gameId));

        return $response;
    }

    public static function bulkUpdateStatuses(): RedirectResponse
    {
        $validated = request()->validate([
            'game_ids' => ['required', 'array'],
            'game_ids.*' => ['required'],
            'status' => ['required', 'string'],
        ]);

        foreach ($validated['game_ids'] as $gameId) {
            UserGameMeta::query()->updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'game_id' => (string) $gameId,
                ],
                [
                    'status' => $validated['status'],
                ]
            );
        }

        self::flushUserCache(Auth::id());

        return back()->with(
            'success',
            'Statuses updated successfully.'
        );
    }

    public static function steamSearch(
        SteamService $steam
    ): JsonResponse {
        $query = request('q');

        return response()->json(
            $query
                ? Cache::remember(
                    CacheKeys::steamSearch($query),
                    now()->addMinutes(15),
                    fn () => $steam->searchStore($query)
                )
                : []
        );
    }

    private static function featuredGames(
        User $user,
        $games
    ): array {
        return Cache::remember(
            CacheKeys::featuredGames($user->id),
            now()->addMinutes(30),
            fn () => UserGameMeta::query()
                ->where('user_id', $user->id)
                ->where('show_on_public_profile', true)
                ->latest('updated_at')
                ->limit(6)
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
                ->toArray()
        );
    }

    private static function featuredReviews(User $user): array
    {
        return Cache::remember(
            CacheKeys::featuredReviews($user->id),
            now()->addMinutes(30),
            fn () => PublicReview::query()
                ->where('user_id', $user->id)
                ->where('is_featured_on_profile', true)
                ->latest('updated_at')
                ->limit(6)
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
                ->toArray()
        );
    }

    private static function featuredWardrobeItems(User $user): array
    {
        return Cache::remember(
            CacheKeys::featuredWardrobeItems($user->id),
            now()->addMinutes(30),
            fn () => UserShopItem::query()
                ->with('item')
                ->where('user_id', $user->id)
                ->where('is_featured_on_profile', true)
                ->latest('updated_at')
                ->limit(6)
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
                ->toArray()
        );
    }

    private static function flushUserCache(int $userId): void
    {
        Cache::forget(CacheKeys::userBase($userId));
        Cache::forget(CacheKeys::userStatuses($userId));
        Cache::forget(CacheKeys::userCustomLabels($userId));
        Cache::forget(CacheKeys::userLibrary($userId));
        Cache::forget(CacheKeys::userLibraryForUser($userId));
        Cache::forget(CacheKeys::userWishlist($userId));
        Cache::forget(CacheKeys::userActivity($userId));
        Cache::forget(CacheKeys::userEquippedItems($userId));
        Cache::forget(CacheKeys::profilePage($userId));
        Cache::forget(CacheKeys::publicProfile($userId));
        Cache::forget(CacheKeys::featuredGames($userId));
        Cache::forget(CacheKeys::featuredReviews($userId));
        Cache::forget(CacheKeys::featuredWardrobeItems($userId));

        foreach (['Backlog', 'Playing', 'Finished', 'Dropped'] as $status) {
            Cache::forget(CacheKeys::userLibraryStatus($userId, $status));
        }
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