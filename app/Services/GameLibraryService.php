<?php

namespace App\Services;

use App\Http\Requests\StoreCustomGameRequest;
use App\Models\CustomGame;
use App\Models\UserGameMeta;
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
        return [
            ...$this->steamLibraryGames($steam),
            ...$this->customGames(),
        ];
    }

    public function steamLibraryGames(SteamService $steam): array
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
                    'cover_url' => $this->steamCoverUrl($gameId),
                    'is_custom' => false,
                    ...$this->meta->existingMetaFor($gameId),
                ];
            })
            ->toArray();
    }

    public function customGames(): array
    {
        return Auth::user()
            ->customGames()
            ->get()
            ->map(function ($game) {
                $gameId = $this->customGameId($game->id);

                return [
                    'id' => $gameId,
                    'appid' => null,
                    'name' => $game->title,
                    'title' => $game->title,
                    'publisher' => $game->publisher,
                    'cover_url' => $game->cover_url,
                    'playtime_forever' => 0,
                    'is_custom' => true,
                    ...$this->meta->existingMetaFor($gameId),
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
        $games = collect($this->allGames($steam))
            ->keyBy(fn ($game) => (string) $game['id']);

        return UserGameMeta::where('user_id', Auth::id())
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

    private function activityPayload(UserGameMeta $meta, Collection $games): array
    {
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