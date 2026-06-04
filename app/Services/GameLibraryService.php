<?php

namespace App\Services;

use App\Http\Requests\StoreCustomGameRequest;
use App\Models\CustomGame;
use App\Models\User;
use App\Models\UserGameMeta;
use App\Helpers\GameTitleNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class GameLibraryService
{
    public function __construct(
        private GameMetaService $meta,
        private StatusService $statuses
    ) {}

    public function allGames(SteamService $steam): array
    {
        return $this->allGamesForUser(
            Auth::user(),
            $steam
        );
    }

    public function allGamesForUser(
        User $user,
        SteamService $steam
    ): array {
        return [
            ...$this->steamLibraryGamesForUser($user, $steam),
            ...$this->customGamesForUser($user),
        ];
    }

    public function steamLibraryGames(SteamService $steam): array
    {
        return $this->steamLibraryGamesForUser(
            Auth::user(),
            $steam
        );
    }

    public function steamLibraryGamesForUser(
        User $user,
        SteamService $steam
    ): array {
        if (! $user->steam_id) {
            return [];
        }

        return collect($steam->getOwnedGames($user->steam_id))
            ->map(function (array $game) use ($user) {
                $gameId = (string) $game['appid'];

                return [
                    ...$game,
                    'id' => $game['appid'],
                    'title' => $game['name'] ?? null,
                    'cover_url' => $this->steamCoverUrl($gameId),
                    'is_custom' => false,
                    'source' => 'steam',
                    ...$this->existingMetaForUser($user, $gameId),
                ];
            })
            ->toArray();
    }

    public function customGames(): array
    {
        return $this->customGamesForUser(
            Auth::user()
        );
    }

    public function customGamesForUser(User $user): array
    {
        return $user
            ->customGames()
            ->get()
            ->map(function ($game) use ($user) {
                $gameId = $this->customGameId($game->id);

                return [
                    'id' => $gameId,
                    'appid' => null,
                    'igdb_id' => $game->igdb_id,
                    'name' => $game->title,
                    'title' => $game->title,
                    'publisher' => $game->publisher,
                    'cover_url' => $game->cover_url,
                    'playtime_forever' => 0,
                    'is_custom' => true,
                    'source' => $game->source ?? 'manual',
                    ...$this->existingMetaForUser($user, $gameId),
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
        return collect($this->allGames($steam))
            ->filter(fn ($game) =>
                $this->normalizedStatus($game['status'] ?? null)
                    === $this->normalizedStatus($status)
            )
            ->values()
            ->toArray();
    }

    public function activityLog(SteamService $steam): array
    {
        $user = Auth::user();

        $games = collect($this->allGamesForUser($user, $steam))
            ->keyBy(fn ($game) => (string) $game['id']);

        return UserGameMeta::where('user_id', $user->id)
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->filter(fn ($meta) => $this->hasVisibleActivity($meta))
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

        if (! $existingGame) {
            $existingGame = CustomGame::create([
                'user_id' => Auth::id(),
                'igdb_id' => $validated['igdb_id'] ?? null,
                'title' => $validated['title'],
                'normalized_title' => $normalizedTitle,
                'publisher' => $validated['publisher'] ?? null,
                'cover_url' => $validated['cover_url'] ?? null,
                'source' => $validated['source'] ?? 'manual',
            ]);
        } else {
            $existingGame->fill([
                'igdb_id' => $existingGame->igdb_id ?? ($validated['igdb_id'] ?? null),
                'publisher' => $existingGame->publisher ?? ($validated['publisher'] ?? null),
                'cover_url' => $existingGame->cover_url ?? ($validated['cover_url'] ?? null),
                'source' => $existingGame->source ?? ($validated['source'] ?? 'manual'),
            ])->save();
        }

        return redirect()->route('dashboard');
    }

    public function existingMetaForUser(
        User $user,
        string $gameId
    ): array {
        $meta = UserGameMeta::where('user_id', $user->id)
            ->where('game_id', $gameId)
            ->first();

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