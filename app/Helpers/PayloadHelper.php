<?php

namespace App\Helpers;

use App\Http\Requests\StoreCustomGameRequest;
use App\Http\Requests\StoreCustomLabelRequest;
use App\Http\Requests\UpdateCustomLabelRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use App\Http\Requests\UpdateGameMetaRequest;
use App\Models\PublicReview;
use App\Models\BlogPost;
use App\Models\CustomList;
use App\Models\PublicReviewReport;
use App\Models\User;
use App\Models\CustomStatus;
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
    /**
     * Keep view caches deliberately short. Cache invalidation covers writes made
     * through this helper, but changes made by jobs, observers or other services
     * must also become visible without waiting for tens of minutes.
     */
    private const VIEW_CACHE_TTL_SECONDS = 60;
    private const LOOKUP_CACHE_TTL_SECONDS = 300;
    private const EXTERNAL_CACHE_TTL_SECONDS = 600;

    public static function pageData(SteamService $steam): array
    {
        $userId = Auth::id();
        $games = Cache::remember(
            CacheKeys::userLibrary($userId),
            self::externalCacheTtl(),
            fn () => self::library()->allGames($steam)
        );

        return [
            ...self::basePageData(),
            'games' => self::withFreshUserMetadata($games, $userId),
        ];
    }

    private static function basePageData(): array
    {
        $userId = Auth::id();

        return Cache::remember(
            CacheKeys::userBase($userId),
            self::viewCacheTtl(),
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
            self::viewCacheTtl(),
            function () use ($user, $steam) {
                $games = collect(
                    Cache::remember(
                        CacheKeys::userLibraryForUser($user->id),
                        self::viewCacheTtl(),
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
                    'publicCustomLists' => self::publicCustomLists($user),
                ];
            }
        );
    }

    public static function publicProfileContent(User $user): array
    {
        $viewerId = Auth::id();

        $reviews = PublicReview::query()
            ->where('user_id', $user->id)
            ->where('is_public', true)
            ->with('votes:id,public_review_id,user_id,value')
            ->latest()
            ->get()
            ->map(fn (PublicReview $review) => [
                'id' => $review->id,
                'title' => $review->title,
                'body' => $review->body,
                'rating' => $review->rating,
                'recommended' => $review->recommended,
                'not_recommended' => $review->not_recommended,
                'game_title' => $review->game_title,
                'created_at' => $review->created_at?->diffForHumans(),
                'score' => (int) $review->votes->sum('value'),
                'user_vote' => $viewerId
                    ? $review->votes->firstWhere('user_id', $viewerId)?->value
                    : null,
                'can_interact' => $viewerId && $viewerId !== $review->user_id,
                'url' => route('reviews.public.show', $review),
            ])
            ->values();

        $posts = BlogPost::query()
            ->where('user_id', $user->id)
            ->where('is_published', true)
            ->with('votes:id,blog_post_id,user_id,value')
            ->latest('published_at')
            ->get()
            ->map(fn (BlogPost $post) => [
                'id' => $post->id,
                'slug' => $post->slug,
                'title' => $post->title,
                'excerpt' => $post->excerpt
                    ?: \Illuminate\Support\Str::limit(
                        \Illuminate\Support\Str::squish(strip_tags($post->body)),
                        240
                    ),
                'published_at' => $post->published_at?->diffForHumans(),
                'score' => (int) $post->votes->sum('value'),
                'user_vote' => $viewerId
                    ? $post->votes->firstWhere('user_id', $viewerId)?->value
                    : null,
                'can_interact' => $viewerId && $viewerId !== $post->user_id,
                'url' => route('blog.show', $post),
            ])
            ->values();

        return [
            'publicReviews' => $reviews,
            'publicBlogPosts' => $posts,
            'viewerAuthenticated' => Auth::check(),
            'isOwnProfile' => $viewerId === $user->id,
        ];
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
        $games = Cache::remember(
            CacheKeys::userLibrary($userId),
            self::externalCacheTtl(),
            fn () => self::library()->allGames($steam)
        );

        $games = collect(self::withFreshUserMetadata($games, $userId))
            ->filter(fn (array $game) => $game['status'] === $status)
            ->values()
            ->toArray();

        return [
            ...self::basePageData(),
            'games' => $games,
        ];
    }

    public static function wishlistPageData(SteamService $steam): array
    {
        $userId = Auth::id();

        return [
            ...self::basePageData(),
            'games' => self::withFreshUserMetadata(
                Cache::remember(
                    CacheKeys::userWishlist($userId),
                    self::externalCacheTtl(),
                    fn () => self::library()->wishlistGames($steam)
                ),
                $userId
            ),
        ];
    }

    public static function profilePageData(SteamService $steam): array
    {
        $user = Auth::user();

        return Cache::remember(
            CacheKeys::profilePage($user->id),
            self::viewCacheTtl(),
            function () use ($user, $steam) {
                $gamesForUser = collect(
                    Cache::remember(
                        CacheKeys::userLibraryForUser($user->id),
                        self::viewCacheTtl(),
                        fn () => self::library()->allGamesForUser($user, $steam)
                    )
                )->keyBy(fn ($game) => (string) $game['id']);

                return [
                    'user' => self::currentUser(),

                    'games' => Cache::remember(
                        CacheKeys::userLibrary($user->id),
                        self::viewCacheTtl(),
                        fn () => self::library()->allGames($steam)
                    ),

                    'activity' => Cache::remember(
                        CacheKeys::userActivity($user->id),
                        self::viewCacheTtl(),
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
        $game = Cache::remember(
            CacheKeys::gameDetails($gameId),
            self::externalCacheTtl(),
            fn () => self::details()->gameDetails($gameId, $steam)
        );

        // User metadata must never be stored in the shared game-details cache.
        // Read it on every request so a full page refresh always reflects the
        // latest note, rating, recommendation and status.
        $meta = UserGameMeta::query()
            ->where('user_id', Auth::id())
            ->where('game_id', (string) $gameId)
            ->first();

        return [
            'user' => self::currentUser(),
            'statuses' => self::statuses(),
            'game' => [
                ...$game,
                'status' => $meta?->status,
                'note' => $meta?->note,
                'rating' => $meta?->rating,
                'recommended' => (bool) ($meta?->recommended ?? false),
                'not_recommended' => (bool) ($meta?->not_recommended ?? false),
                'show_on_public_profile' => (bool) (
                    $meta?->show_on_public_profile ?? false
                ),
            ],
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
            self::viewCacheTtl(),
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
            self::viewCacheTtl(),
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
            self::lookupCacheTtl(),
            fn () => self::status()->statuses()
        );
    }

    public static function customLabels(): array
    {
        return Cache::remember(
            CacheKeys::userCustomLabels(Auth::id()),
            self::lookupCacheTtl(),
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

    public static function updateCustomLabel(
        UpdateCustomLabelRequest $request,
        CustomStatus $customLabel
    ): RedirectResponse {
        $customLabel->update([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        self::flushUserCache(Auth::id());

        return back();
    }

    public static function deleteCustomLabel(
        CustomStatus $customLabel
    ): RedirectResponse {
        $customLabel->delete();

        self::flushUserCache(Auth::id());

        return back();
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
                    self::externalCacheTtl(),
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
            self::viewCacheTtl(),
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
            self::viewCacheTtl(),
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
            self::viewCacheTtl(),
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

    /**
     * Merge volatile per-user fields into cached library data. The cache may
     * contain an old status, so every metadata field is explicitly replaced,
     * including with null/false when the row no longer exists.
     */
    private static function withFreshUserMetadata(
        iterable $games,
        int $userId
    ): array {
        $metadata = UserGameMeta::query()
            ->where('user_id', $userId)
            ->get()
            ->keyBy(fn (UserGameMeta $meta) => (string) $meta->game_id);

        return collect($games)
            ->map(function (array $game) use ($metadata) {
                $meta = $metadata->get((string) $game['id']);

                return [
                    ...$game,
                    'status' => $meta?->status,
                    'note' => $meta?->note,
                    'rating' => $meta?->rating,
                    'recommended' => (bool) (
                        $meta?->recommended ?? false
                    ),
                    'not_recommended' => (bool) (
                        $meta?->not_recommended ?? false
                    ),
                    'show_on_public_profile' => (bool) (
                        $meta?->show_on_public_profile ?? false
                    ),
                ];
            })
            ->values()
            ->toArray();
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

    private static function viewCacheTtl(): \DateTimeInterface
    {
        return now()->addSeconds(self::VIEW_CACHE_TTL_SECONDS);
    }

    private static function lookupCacheTtl(): \DateTimeInterface
    {
        return now()->addSeconds(self::LOOKUP_CACHE_TTL_SECONDS);
    }

    private static function externalCacheTtl(): \DateTimeInterface
    {
        return now()->addSeconds(self::EXTERNAL_CACHE_TTL_SECONDS);
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

    private static function publicCustomLists(User $user): array
    {
        return CustomList::query()
            ->withCount('items')
            ->where('user_id', $user->id)
            ->where('visibility', 'public')
            ->latest()
            ->get()
            ->map(fn (CustomList $list) => [
                'id' => $list->id,
                'title' => $list->title,
                'slug' => $list->slug,
                'description' => $list->description,
                'items_count' => $list->items_count,
                'created_at' => $list->created_at?->diffForHumans(),

                'items' => $list->items
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'game_id' => $item->game_id,
                    'title' => $item->game_title
                        ?? $item->title
                        ?? $item->name
                        ?? $item->game_id
                        ?? 'Unknown game',
                    'cover_url' => $item->game_cover_url,
                    'position' => $item->position,
                ])
                ->values()
                ->toArray(),
            ])
            ->values()
            ->toArray();
    }
}
