<?php

namespace App\Services;

use App\Helpers\GameTitleNormalizer;
use App\Http\Requests\StoreCustomGameRequest;
use App\Models\CustomGame;
use App\Models\User;
use App\Models\UserGameMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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
        $metas = $this->metasForUser($user);

        return [
            ...$this->steamLibraryGamesForUser($user, $steam, $metas),
            ...$this->customGamesForUser($user, $metas),
        ];
    }

    public function steamLibraryGames(SteamService $steam): array
    {
        return $this->steamLibraryGamesForUser(
            Auth::user(),
            $steam,
            $this->metasForUser(Auth::user())
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

        $metas ??= $this->metasForUser($user);

        return collect($steam->getOwnedGames($user->steam_id))
            ->map(function (array $game) use ($game = null, $metas) {
                $gameId = (string) $game['appid'];

                return [
                    ...$game,
                    'id' => $game['appid'],
                    'title' => $game['name'] ?? null,
                    'cover_url' => $this->steamCoverUrl($gameId),
                    'is_custom' => false,
                    'source' => 'steam',
                    ...$this->metaPayloadFromCollection($metas, $gameId),
                ];
            })
            ->toArray();
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
        $metas ??= $this->metasForUser($user);

        return $user
            ->customGames()
            ->get()
            ->map(function ($game) use ($metas) {
                $gameId = $this->customGameId($game->id);

                return [
                    'id' => $gameId,
                    'appid' => null,
                    'igdb_id' => $game->igdb_id,
                    'igdb_slug' => $game->igdb_slug,
                    'igdb_url' => $game->igdb_url,
                    'name' => $game->title,
                    'title' => $game->title,
                    'publisher' => $game->publisher,
                    'developer' => $game->developer,
                    'description' => $game->description,
                    'release_date' => $game->release_date?->format('Y-m-d'),
                    'cover_url' => $game->cover_url,
                    'header_image_url' => $game->header_image_url,
                    'playtime_forever' => 0,
                    'is_custom' => true,
                    'source' => $game->source ?? 'manual',
                    'platform' => $game->platform,
                    ...$this->metaPayloadFromCollection($metas, $gameId),
                ];
            })
            ->toArray();
    }

    public function wishlistGames(SteamService $steam): array
    {
        $user = Auth::user();

        if (! $user->steam_id) {
            return [];
        }

        return $steam->getWishlist($user->steam_id);
    }

    public function gamesByStatus(
        SteamService $steam,
        string $status
    ): array {
        $normalizedStatus = $this->normalizedStatus($status);

        return collect($this->allGames($steam))
            ->filter(fn ($game) =>
                $this->normalizedStatus($game['status'] ?? null) === $normalizedStatus
            )
            ->values()
            ->toArray();
    }

    public function activityLog(SteamService $steam): array
    {
        $user = Auth::user();

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

    public function storeCustomGame(StoreCustomGameRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $normalizedTitle = GameTitleNormalizer::normalize($validated['title']);

        $existingGame = CustomGame::query()
            ->where('user_id', Auth::id())
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
                'user_id' => Auth::id(),
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
        return $this->metaCache[$user->id]
            ??= UserGameMeta::query()
                ->where('user_id', $user->id)
                ->get()
                ->keyBy(fn ($meta) => (string) $meta->game_id);
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
}