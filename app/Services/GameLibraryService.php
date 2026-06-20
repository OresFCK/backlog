<?php

namespace App\Services;

use App\Helpers\GameTitleNormalizer;
use App\Http\Requests\StoreCustomGameRequest;
use App\Models\CustomGame;
use App\Models\SteamGameAchievementCache;
use App\Models\User;
use App\Models\UserGameMeta;
use App\Models\UserSteamGame;
use App\Helpers\CacheKeys;
use App\Helpers\UserCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class GameLibraryService
{
    private array $metaCache = [];

    public function __construct(
        private GameMetaService $meta,
        private StatusService $statuses
    ) {}

    public function allGames(SteamService $steam): array
    {
        return $this->allGamesForUser(Auth::user(), $steam);
    }

    public function allGamesForUser(User $user, SteamService $steam): array
    {
        return Cache::remember(
            "user:{$user->id}:library:all",
            now()->addMinutes(30),
            function () use ($user, $steam) {
                $metas = $this->metasForUser($user);

                return collect([
                    ...$this->steamLibraryGamesForUser($user, $steam, $metas),
                    ...$this->customGamesForUser($user, $metas),
                ])
                    ->filter(fn ($game) => ! empty($game['id']))
                    ->values()
                    ->toArray();
            }
        );
    }

    public function steamLibraryGames(SteamService $steam): array
    {
        $user = Auth::user();

        return $this->steamLibraryGamesForUser(
            $user,
            $steam,
            $this->metasForUser($user)
        );
    }

    public function steamLibraryGamesForUser(
        User $user,
        SteamService $steam,
        ?Collection $metas = null
    ): array {
        if (! $user->steam_id) {
            return [];
        }

        return Cache::remember(
            "user:{$user->id}:library:steam",
            now()->addMinutes(30),
            function () use ($user, $steam, $metas) {
                $metas ??= $this->metasForUser($user);

                $achievementCache = SteamGameAchievementCache::query()
                    ->where('user_id', $user->id)
                    ->get()
                    ->keyBy(fn ($row) => (string) $row->steam_app_id);

                return UserSteamGame::query()
                    ->where('user_id', $user->id)
                    ->get()
                    ->map(function (UserSteamGame $game) use ($metas, $achievementCache) {
                        $gameId = (string) $game->steam_app_id;
                        $achievement = $achievementCache->get($gameId);

                        return [
                            'id' => $gameId,
                            'appid' => $gameId,
                            'name' => $game->name ?? 'Unknown game',
                            'title' => $game->name ?? 'Unknown game',
                            'cover_url' => $this->steamCoverUrl($gameId),

                            'playtime_forever' => (int) ($game->playtime_forever ?? 0),
                            'playtime_hours' => round(((int) ($game->playtime_forever ?? 0)) / 60, 1),

                            'is_custom' => false,
                            'source' => 'steam',

                            'achievements_unlocked' => (int) ($achievement?->unlocked ?? 0),
                            'achievements_total' => (int) ($achievement?->total ?? 0),
                            'achievement_percent' => (int) ($achievement?->percent ?? 0),

                            ...$this->metaPayloadFromCollection($metas, $gameId),
                        ];
                    })
                    ->values()
                    ->toArray();
            }
        );
    }

    public function customGames(): array
    {
        $user = Auth::user();

        return $this->customGamesForUser(
            $user,
            $this->metasForUser($user)
        );
    }

    public function customGamesForUser(
        User $user,
        ?Collection $metas = null
    ): array {
        return Cache::remember(
            "user:{$user->id}:library:custom",
            now()->addMinutes(30),
            function () use ($user, $metas) {
                $metas ??= $this->metasForUser($user);

                return $user
                    ->customGames()
                    ->get()
                    ->filter(fn (CustomGame $game) => filled($game->title))
                    ->map(function (CustomGame $game) use ($metas) {
                        $gameId = $this->customGameId($game->id);

                        $achievementsUnlocked = (int) ($game->achievements_unlocked ?? 0);
                        $achievementsTotal = (int) ($game->achievements_total ?? 0);

                        return [
                            'id' => $gameId,
                            'appid' => $gameId,

                            'igdb_id' => $game->igdb_id,
                            'igdb_slug' => $game->igdb_slug,
                            'igdb_url' => $game->igdb_url,

                            'name' => $game->title ?: 'Unknown game',
                            'title' => $game->title ?: 'Unknown game',

                            'publisher' => $game->publisher,
                            'developer' => $game->developer,
                            'description' => $game->description,
                            'release_date' => $game->release_date?->format('Y-m-d'),

                            'cover_url' => $game->cover_url ?: null,
                            'header_image_url' => $game->header_image_url ?: null,

                            'playtime_forever' => (int) ($game->playtime_minutes ?? 0),
                            'playtime_hours' => $game->playtime_minutes !== null
                                ? round(((int) $game->playtime_minutes) / 60, 1)
                                : 0,

                            'achievements_unlocked' => $achievementsUnlocked,
                            'achievements_total' => $achievementsTotal,
                            'achievement_percent' => $achievementsTotal > 0
                                ? (int) round(($achievementsUnlocked / $achievementsTotal) * 100)
                                : 0,

                            'is_custom' => true,
                            'source' => $game->source ?: 'manual',
                            'platform' => $game->platform,
                            'custom_game_id' => $game->id,

                            ...$this->metaPayloadFromCollection($metas, $gameId),
                        ];
                    })
                    ->values()
                    ->toArray();
            }
        );
    }

    public function wishlistGames(SteamService $steam): array
    {
        $user = Auth::user();

        if (! $user->steam_id) {
            return [];
        }

        return Cache::remember(
            "user:{$user->id}:library:wishlist",
            now()->addMinutes(30),
            fn () => $steam->getWishlist($user->steam_id)
        );
    }

    public function gamesByStatus(
        SteamService $steam,
        string $status
    ): array {
        $userId = Auth::id();
        $normalizedStatus = $this->normalizedStatus($status);

        return Cache::remember(
            "user:{$userId}:library:status:{$normalizedStatus}",
            now()->addMinutes(30),
            fn () => collect($this->allGames($steam))
                ->filter(fn ($game) =>
                    $this->normalizedStatus($game['status'] ?? null) === $normalizedStatus
                )
                ->values()
                ->toArray()
        );
    }

    public function activityLog(SteamService $steam): array
    {
        $user = Auth::user();

        return Cache::remember(
            "user:{$user->id}:activity-log",
            now()->addMinutes(15),
            function () use ($user, $steam) {
                $games = collect($this->allGamesForUser($user, $steam))
                    ->keyBy(fn ($game) => (string) $game['id']);

                return UserGameMeta::query()
                    ->where('user_id', $user->id)
                    ->latest('updated_at')
                    ->limit(30)
                    ->get()
                    ->filter(fn ($meta) => $this->hasVisibleActivity($meta))
                    ->take(10)
                    ->map(fn ($meta) => $this->activityPayload($meta, $games))
                    ->values()
                    ->toArray();
            }
        );
    }

    public function storeCustomGame(StoreCustomGameRequest $request): RedirectResponse
    {
        $userId = Auth::id();
        $validated = $request->validated();
        $normalizedTitle = GameTitleNormalizer::normalize($validated['title']);

        $existingGame = CustomGame::query()
            ->where('user_id', $userId)
            ->where(function ($query) use ($validated, $normalizedTitle) {
                $query->where('normalized_title', $normalizedTitle);

                if (! empty($validated['igdb_id'])) {
                    $query->orWhere('igdb_id', $validated['igdb_id']);
                }
            })
            ->first();

        $payload = [
            'igdb_id' => $validated['igdb_id'] ?? null,
            'igdb_slug' => $validated['igdb_slug'] ?? null,
            'igdb_url' => $validated['igdb_url'] ?? null,
            'title' => $validated['title'],
            'normalized_title' => $normalizedTitle,
            'publisher' => $validated['publisher'] ?? null,
            'developer' => $validated['developer'] ?? null,
            'description' => $validated['description'] ?? null,
            'release_date' => $validated['release_date'] ?? null,
            'cover_url' => $validated['cover_url'] ?? null,
            'header_image_url' => $validated['header_image_url'] ?? null,
            'source' => $validated['source'] ?? 'manual',
            'platform' => $validated['platform'] ?? null,
        ];

        if (! $existingGame) {
            CustomGame::create([
                'user_id' => $userId,
                ...$payload,
            ]);
        } else {
            $existingGame->fill([
                'igdb_id' => $existingGame->igdb_id ?? $payload['igdb_id'],
                'igdb_slug' => $existingGame->igdb_slug ?? $payload['igdb_slug'],
                'igdb_url' => $existingGame->igdb_url ?? $payload['igdb_url'],
                'publisher' => $existingGame->publisher ?? $payload['publisher'],
                'developer' => $existingGame->developer ?? $payload['developer'],
                'description' => $existingGame->description ?? $payload['description'],
                'release_date' => $existingGame->release_date ?? $payload['release_date'],
                'cover_url' => $existingGame->cover_url ?? $payload['cover_url'],
                'header_image_url' => $existingGame->header_image_url ?? $payload['header_image_url'],
                'source' => $existingGame->source ?? $payload['source'],
                'platform' => $existingGame->platform ?? $payload['platform'],
            ])->save();
        }

        $this->flushLibraryCache($userId);
        UserCache::flush($userId);

        return redirect()->route('dashboard');
    }

    public function existingMetaForUser(User $user, string $gameId): array
    {
        return $this->metaPayloadFromCollection(
            $this->metasForUser($user),
            $gameId
        );
    }

    private function metasForUser(User $user): Collection
    {
        if (isset($this->metaCache[$user->id])) {
            return $this->metaCache[$user->id];
        }

        return $this->metaCache[$user->id] = Cache::remember(
            CacheKeys::userMetas($user->id),
            now()->addMinutes(30),
            fn () => UserGameMeta::query()
                ->where('user_id', $user->id)
                ->get()
                ->keyBy(fn ($meta) => (string) $meta->game_id)
        );
    }

    private function metaPayloadFromCollection(Collection $metas, string $gameId): array
    {
        $meta = $metas->get((string) $gameId);

        return $meta
            ? $this->meta->metaPayload($meta)
            : $this->meta->emptyMeta();
    }

    private function activityPayload(
        UserGameMeta $meta,
        Collection $games
    ): array {
        $game = $games->get((string) $meta->game_id);
        $status = $meta->status ?? 'Backlog';

        return [
            'id' => $meta->game_id,
            'title' => $game['title'] ?? $game['name'] ?? 'Unknown game',
            'cover_url' => $game['cover_url'] ?? null,
            'status' => $status,
            'status_color' => $this->statuses->statusColor($status),
            'rating' => $meta->rating,
            'recommended' => $meta->recommended ?? false,
            'not_recommended' => $meta->not_recommended ?? false,
            'note' => $meta->note,
            'updated_at' => $meta->updated_at?->diffForHumans(),
        ];
    }

    private function hasVisibleActivity(UserGameMeta $meta): bool
    {
        return filled($meta->rating)
            || filled($meta->note)
            || $meta->recommended
            || (
                filled($meta->status)
                && strtolower($meta->status) !== 'backlog'
            );
    }

    private function normalizedStatus(?string $status): string
    {
        return strtolower(trim($status ?? 'Backlog'));
    }

    private function steamCoverUrl(string $gameId): string
    {
        return "https://cdn.cloudflare.steamstatic.com/steam/apps/{$gameId}/header.jpg";
    }

    private function customGameId(int|string $id): string
    {
        return 'custom-' . $id;
    }

    private function flushLibraryCache(int $userId): void
    {
        Cache::forget("user:{$userId}:library:all");
        Cache::forget("user:{$userId}:library:steam");
        Cache::forget("user:{$userId}:library:custom");
        Cache::forget("user:{$userId}:library:wishlist");
        Cache::forget("user:{$userId}:activity-log");

        foreach (['backlog', 'playing', 'finished', 'planned', 'dropped'] as $status) {
            Cache::forget("user:{$userId}:library:status:{$status}");
        }
    }
}