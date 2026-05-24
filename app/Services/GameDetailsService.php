<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class GameDetailsService
{
    public function __construct(
        private GameMetaService $meta
    ) {}

    public function gameDetails(string $gameId, SteamService $steam): array
    {
        return $this->isCustomGame($gameId)
            ? $this->customGameDetails($gameId)
            : $this->steamGameDetails($gameId, $steam);
    }

    public function customGameDetails(string $gameId): array
    {
        $customGame = Auth::user()
            ->customGames()
            ->findOrFail($this->customGameDatabaseId($gameId));

        $meta = $this->meta->metaFor($gameId);

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
            ...$this->meta->metaPayload($meta),
        ];
    }

    public function steamGameDetails(string $gameId, SteamService $steam): array
    {
        $user = Auth::user();
        $details = $steam->getAppDetails($gameId);

        abort_if(! $details, 404);

        $ownedGame = collect($steam->getOwnedGames($user->steam_id))
            ->firstWhere('appid', (int) $gameId);

        $achievements = $steam->getPlayerAchievements($user->steam_id, $gameId);
        $meta = $this->meta->metaFor($gameId);

        return [
            'id' => $gameId,
            'appid' => $gameId,
            'title' => $details['name'] ?? 'Unknown game',
            'publisher' => $details['publishers'][0] ?? null,
            'cover_url' => $details['capsule_imagev5']
                ?? $details['header_image']
                ?? $this->steamCoverUrl($gameId),
            'header_image' => $details['header_image'] ?? null,
            'description' => strip_tags($details['short_description'] ?? ''),
            'about' => strip_tags($details['about_the_game'] ?? ''),
            'developers' => $details['developers'] ?? [],
            'publishers' => $details['publishers'] ?? [],
            'genres' => $this->genreNames($details),
            'screenshots' => $this->screenshotUrls($details),
            'platforms' => $details['platforms'] ?? [],
            'release_date' => $details['release_date']['date'] ?? null,
            'steam_url' => "https://store.steampowered.com/app/{$gameId}",
            'playtime_hours' => $this->playtimeHours($ownedGame),
            'achievements_unlocked' => $achievements['unlocked'] ?? null,
            'achievements_total' => $achievements['total'] ?? null,
            'is_custom' => false,
            ...$this->meta->metaPayload($meta),
        ];
    }

    private function genreNames(array $details): array
    {
        return collect($details['genres'] ?? [])
            ->pluck('description')
            ->values()
            ->all();
    }

    private function screenshotUrls(array $details): array
    {
        return collect($details['screenshots'] ?? [])
            ->pluck('path_full')
            ->take(6)
            ->values()
            ->all();
    }

    private function playtimeHours(?array $ownedGame): ?float
    {
        if (! $ownedGame) {
            return null;
        }

        return round(($ownedGame['playtime_forever'] ?? 0) / 60, 1);
    }

    private function steamCoverUrl(string $gameId): string
    {
        return "https://cdn.cloudflare.steamstatic.com/steam/apps/{$gameId}/header.jpg";
    }

    private function isCustomGame(string $gameId): bool
    {
        return str_starts_with($gameId, 'custom-');
    }

    private function customGameDatabaseId(string $gameId): string
    {
        return str_replace('custom-', '', $gameId);
    }
}