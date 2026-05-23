<?php

namespace App\Helpers;

use App\Http\Requests\StoreCustomGameRequest;
use App\Http\Requests\StoreCustomLabelRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use App\Http\Requests\UpdateGameMetaRequest;
use App\Models\CustomGame;
use App\Models\UserGameMeta;
use App\Services\SteamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class PayloadHelper
{
    public static function pageData(SteamService $steam): array
    {
        return [
            'user' => self::currentUser(),
            'games' => self::allGames($steam),
            'statuses' => self::statuses(),
        ];
    }

    public static function profilePageData(SteamService $steam): array
    {
        return [
            'user' => self::currentUser(),
            'games' => self::allGames($steam),
            'activity' => self::activityLog($steam),
        ];
    }

    public static function wishlistPageData(SteamService $steam): array
    {
        return [
            ...self::pageData($steam),
            'games' => self::wishlistGames($steam),
        ];
    }

    public static function gamePageData(string $gameId, SteamService $steam): array
    {
        return [
            'user' => self::currentUser(),
            'statuses' => self::statuses(),
            'game' => self::gameDetails($gameId, $steam),
        ];
    }

    public static function currentUser(): array
    {
        $user = Auth::user();

        return [
            'name' => $user->name,
            'steam_id' => $user->steam_id,
            'avatar' => $user->steam_avatar_url,
            'banner_url' => $user->banner_url,
        ];
    }

    public static function allGames(SteamService $steam): array
    {
        return [
            ...self::steamLibraryGames($steam),
            ...self::customGames(),
        ];
    }

    public static function steamLibraryGames(SteamService $steam): array
    {
        $user = Auth::user();

        if (! $user->steam_id) {
            return [];
        }

        return collect($steam->getOwnedGames($user->steam_id))
            ->map(function (array $game) {
                $gameId = (string) $game['appid'];

                return [
                    ...$game,
                    'id' => $game['appid'],
                    'cover_url' => self::steamCoverUrl($gameId),
                    'is_custom' => false,
                    ...self::existingMetaFor($gameId),
                ];
            })
            ->toArray();
    }

    public static function wishlistGames(SteamService $steam): array
    {
        $user = Auth::user();

        if (! $user->steam_id) {
            return [];
        }

        return $steam->getWishlist($user->steam_id);
    }

    public static function customGames(): array
    {
        return Auth::user()
            ->customGames()
            ->get()
            ->map(function ($game) {
                $gameId = self::customGameId($game->id);

                return [
                    'id' => $gameId,
                    'appid' => null,
                    'name' => $game->title,
                    'title' => $game->title,
                    'publisher' => $game->publisher,
                    'cover_url' => $game->cover_url,
                    'playtime_forever' => 0,
                    'is_custom' => true,
                    ...self::existingMetaFor($gameId),
                ];
            })
            ->toArray();
    }

    public static function activityLog(SteamService $steam): array
    {
        $games = collect(self::allGames($steam))
            ->keyBy(fn ($game) => (string) $game['id']);

        return UserGameMeta::where('user_id', Auth::id())
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->filter(fn ($meta) => self::hasVisibleActivity($meta))
            ->map(fn ($meta) => self::activityPayload($meta, $games))
            ->values()
            ->toArray();
    }

    public static function gameDetails(string $gameId, SteamService $steam): array
    {
        return self::isCustomGame($gameId)
            ? self::customGameDetails($gameId)
            : self::steamGameDetails($gameId, $steam);
    }

    public static function customGameDetails(string $gameId): array
    {
        $customGame = Auth::user()
            ->customGames()
            ->findOrFail(self::customGameDatabaseId($gameId));

        $meta = self::metaFor($gameId);

        return [
            'id' => $gameId,
            'appid' => null,
            'title' => $customGame->title,
            'publisher' => $customGame->publisher,
            'cover_url' => $customGame->cover_url,
            'header_image' => $customGame->cover_url,
            'description' => null,
            'about' => null,
            'developers' => [],
            'publishers' => $customGame->publisher ? [$customGame->publisher] : [],
            'genres' => [],
            'screenshots' => [],
            'platforms' => [],
            'release_date' => null,
            'steam_url' => null,
            'is_custom' => true,
            ...self::metaPayload($meta),
        ];
    }

    public static function steamGameDetails(string $gameId, SteamService $steam): array
    {
        $user = Auth::user();
        $details = $steam->getAppDetails($gameId);

        abort_if(! $details, 404);

        $ownedGame = collect($steam->getOwnedGames($user->steam_id))
            ->firstWhere('appid', (int) $gameId);

        $achievements = $steam->getPlayerAchievements($user->steam_id, $gameId);
        $meta = self::metaFor($gameId);

        return [
            'id' => $gameId,
            'appid' => $gameId,
            'title' => $details['name'] ?? 'Unknown game',
            'publisher' => $details['publishers'][0] ?? null,
            'cover_url' => $details['capsule_imagev5']
                ?? $details['header_image']
                ?? self::steamCoverUrl($gameId),
            'header_image' => $details['header_image'] ?? null,
            'description' => strip_tags($details['short_description'] ?? ''),
            'about' => strip_tags($details['about_the_game'] ?? ''),
            'developers' => $details['developers'] ?? [],
            'publishers' => $details['publishers'] ?? [],
            'genres' => self::genreNames($details),
            'screenshots' => self::screenshotUrls($details),
            'platforms' => $details['platforms'] ?? [],
            'release_date' => $details['release_date']['date'] ?? null,
            'steam_url' => "https://store.steampowered.com/app/{$gameId}",
            'playtime_hours' => self::playtimeHours($ownedGame),
            'achievements_unlocked' => $achievements['unlocked'] ?? null,
            'achievements_total' => $achievements['total'] ?? null,
            'is_custom' => false,
            ...self::metaPayload($meta),
        ];
    }

    public static function storeCustomGame(StoreCustomGameRequest $request): RedirectResponse
    {
        CustomGame::create([
            'user_id' => Auth::id(),
            ...$request->safe()->only([
                'title',
                'publisher',
                'cover_url',
            ]),
        ]);

        return redirect()->route('dashboard');
    }

    public static function storeStatus(StoreCustomStatusRequest $request): RedirectResponse
    {
        Auth::user()
            ->customStatuses()
            ->create($request->validated());

        return back();
    }

    public static function storeMeta(
        UpdateGameMetaRequest $request,
        string $gameId
    ): RedirectResponse {
        UserGameMeta::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'game_id' => $gameId,
            ],
            [
                ...$request->safe()->only([
                    'note',
                    'rating',
                    'status',
                ]),
                'recommended' => $request->boolean('recommended'),
            ]
        );

        return back();
    }

    public static function steamSearch(SteamService $steam): JsonResponse
    {
        $query = request('q');

        return response()->json(
            $query ? $steam->searchStore($query) : []
        );
    }

    public static function statuses(): array
    {
        $user = Auth::user();

        if (! $user->customStatuses()->exists()) {
            $user->customStatuses()->createMany(self::defaultStatuses());
        }

        return $user
            ->customStatuses()
            ->get()
            ->map(fn ($status) => [
                'id' => $status->id,
                'name' => $status->name,
                'color' => $status->color,
            ])
            ->toArray();
    }

    public static function customLabels(): array
    {
        return Auth::user()
            ->customStatuses()
            ->latest()
            ->get()
            ->map(fn ($status) => [
                'id' => $status->id,
                'title' => $status->name,
                'color' => $status->color,
            ])
            ->toArray();
    }

    public static function storeCustomLabel(StoreCustomLabelRequest $request): RedirectResponse
    {
        Auth::user()
            ->customStatuses()
            ->create([
                'name' => $request->title,
                'color' => $request->color,
            ]);

        return back();
    }

    private static function hasVisibleActivity(UserGameMeta $meta): bool
    {
        return filled($meta->rating)
            || filled($meta->note)
            || $meta->recommended
            || (
                filled($meta->status)
                && strtolower($meta->status) !== 'Backlog'
            );
    }

    private static function activityPayload(UserGameMeta $meta, Collection $games): array
    {
        $game = $games->get((string) $meta->game_id);
        $status = $meta->status ?? 'Backlog';

        return [
            'id' => $meta->game_id,
            'title' => $game['title'] ?? $game['name'] ?? 'Unknown game',
            'cover_url' => $game['cover_url'] ?? null,
            'status' => $status,
            'status_color' => self::statusColor($status),
            'rating' => $meta->rating,
            'recommended' => $meta->recommended ?? false,
            'note' => $meta->note,
            'updated_at' => $meta->updated_at?->diffForHumans(),
        ];
    }

    private static function genreNames(array $details): array
    {
        return collect($details['genres'] ?? [])
            ->pluck('description')
            ->values()
            ->all();
    }

    private static function screenshotUrls(array $details): array
    {
        return collect($details['screenshots'] ?? [])
            ->pluck('path_full')
            ->take(6)
            ->values()
            ->all();
    }

    private static function statusColor(?string $statusName): string
    {
        $status = collect(self::statuses())
            ->firstWhere('name', $statusName);

        return $status['color'] ?? '#71717a';
    }

    private static function steamCoverUrl(string $gameId): string
    {
        return "https://cdn.cloudflare.steamstatic.com/steam/apps/{$gameId}/header.jpg";
    }

    private static function metaFor(string $gameId): UserGameMeta
    {
        return UserGameMeta::firstOrCreate([
            'user_id' => Auth::id(),
            'game_id' => $gameId,
        ]);
    }

    private static function existingMetaFor(string $gameId): array
    {
        $meta = UserGameMeta::where('user_id', Auth::id())
            ->where('game_id', $gameId)
            ->first();

        return $meta
            ? self::metaPayload($meta)
            : self::emptyMeta();
    }

    private static function metaPayload(UserGameMeta $meta): array
    {
        $status = $meta->status ?? 'Backlog';

        return [
            'status' => $status,
            'status_color' => self::statusColor($status),
            'note' => $meta->note,
            'rating' => $meta->rating,
            'recommended' => $meta->recommended ?? false,
            'updated_at' => $meta->updated_at?->diffForHumans(),
        ];
    }

    private static function emptyMeta(): array
    {
        return [
            'status' => 'Backlog',
            'status_color' => self::statusColor('Backlog'),
            'note' => null,
            'rating' => null,
            'recommended' => false,
            'updated_at' => null,
        ];
    }

    private static function playtimeHours(?array $ownedGame): ?float
    {
        if (! $ownedGame) {
            return null;
        }

        return round(($ownedGame['playtime_forever'] ?? 0) / 60, 1);
    }

    private static function isCustomGame(string $gameId): bool
    {
        return str_starts_with($gameId, 'custom-');
    }

    private static function customGameId(int|string $id): string
    {
        return 'custom-' . $id;
    }

    private static function customGameDatabaseId(string $gameId): string
    {
        return str_replace('custom-', '', $gameId);
    }

    private static function defaultStatuses(): array
    {
        return [
            [
                'name' => 'Backlog',
                'color' => '#71717a',
            ],
            [
                'name' => 'Playing',
                'color' => '#3b82f6',
            ],
            [
                'name' => 'Finished',
                'color' => '#22c55e',
            ],
            [
                'name' => 'Planned',
                'color' => '#a855f7',
            ],
            [
                'name' => 'Dropped',
                'color' => '#ef4444',
            ],
        ];
    }
}
