<?php

namespace App\Helpers;

use App\Http\Requests\StoreCustomGameRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use App\Http\Requests\UpdateGameMetaRequest;
use App\Http\Requests\StoreCustomLabelRequest;
use App\Models\CustomGame;
use App\Models\UserGameMeta;
use App\Services\SteamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

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

    public static function wishlistPageData(SteamService $steam): array
    {
        return [
            ...self::pageData($steam),
            'games' => self::wishlistGames($steam),
        ];
    }

    public static function gamePageData(
        string $gameId,
        SteamService $steam
    ): array {
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
            ->map(fn (array $game) => [
                ...$game,
                'id' => $game['appid'],

                ...self::existingMetaFor(
                    (string) $game['appid']
                ),
            ])
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

    public static function gameDetails(
        string $gameId,
        SteamService $steam
    ): array {
        return self::isCustomGame($gameId)
            ? self::customGameDetails($gameId)
            : self::steamGameDetails($gameId, $steam);
    }

    public static function customGameDetails(string $gameId): array
    {
        $user = Auth::user();

        $customGame = $user
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
            'publishers' => $customGame->publisher
                ? [$customGame->publisher]
                : [],
            'genres' => [],
            'screenshots' => [],
            'platforms' => [],
            'release_date' => null,
            'steam_url' => null,
            'is_custom' => true,

            ...self::metaPayload($meta),
        ];
    }

    public static function steamGameDetails(
        string $gameId,
        SteamService $steam
    ): array {
        $user = Auth::user();

        $details = $steam->getAppDetails($gameId);

        abort_if(! $details, 404);

        $ownedGame = collect(
            $steam->getOwnedGames($user->steam_id)
        )->firstWhere('appid', (int) $gameId);

        $achievements = $steam->getPlayerAchievements(
            $user->steam_id,
            $gameId
        );

        $meta = self::metaFor($gameId);

        return [
            'id' => $gameId,
            'appid' => $gameId,
            'title' => $details['name'] ?? 'Unknown game',
            'publisher' => $details['publishers'][0] ?? null,
            'cover_url' => $details['capsule_imagev5']
                ?? $details['header_image']
                ?? null,
            'header_image' => $details['header_image'] ?? null,
            'description' => strip_tags(
                $details['short_description'] ?? ''
            ),
            'about' => strip_tags(
                $details['about_the_game'] ?? ''
            ),
            'developers' => $details['developers'] ?? [],
            'publishers' => $details['publishers'] ?? [],
            'genres' => collect($details['genres'] ?? [])
                ->pluck('description')
                ->values()
                ->all(),
            'screenshots' => collect($details['screenshots'] ?? [])
                ->pluck('path_full')
                ->take(6)
                ->values()
                ->all(),
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

    public static function storeCustomGame(
        StoreCustomGameRequest $request
    ): RedirectResponse {
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

    public static function storeStatus(
        StoreCustomStatusRequest $request
    ): RedirectResponse {
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

    public static function statuses(): array
    {
        $user = Auth::user();

        if (! $user->customStatuses()->exists()) {
            $user
                ->customStatuses()
                ->createMany(self::defaultStatuses());
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
        return [
            'status' => $meta->status ?? 'Backlog',
            'note' => $meta->note,
            'rating' => $meta->rating,
            'recommended' => $meta->recommended ?? false,
        ];
    }

    private static function emptyMeta(): array
    {
        return [
            'status' => 'Backlog',
            'note' => null,
            'rating' => null,
            'recommended' => false,
        ];
    }

    private static function playtimeHours(?array $ownedGame): ?float
    {
        if (! $ownedGame) {
            return null;
        }

        return round(
            ($ownedGame['playtime_forever'] ?? 0) / 60,
            1
        );
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
            ['name' => 'Backlog', 'color' => '#71717a'],
            ['name' => 'Playing', 'color' => '#3b82f6'],
            ['name' => 'Finished', 'color' => '#22c55e'],
            ['name' => 'Planned', 'color' => '#a855f7'],
            ['name' => 'Dropped', 'color' => '#ef4444'],
        ];
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

    public static function storeCustomLabel(
        StoreCustomLabelRequest $request
    ): RedirectResponse {
        Auth::user()
            ->customStatuses()
            ->create([
                'name' => $request->title,
                'color' => $request->color,
            ]);

        return back();
    }
}