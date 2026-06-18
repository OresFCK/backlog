<?php

namespace App\Services;

use App\Helpers\GameTitleNormalizer;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GameDetailsService
{
    private array $appDetailsCache = [];
    private array $ownedGamesCache = [];
    private array $achievementsCache = [];

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

        $achievementsUnlocked = $customGame->achievements_unlocked !== null
            ? (int) $customGame->achievements_unlocked
            : null;

        $achievementsTotal = $customGame->achievements_total !== null
            ? (int) $customGame->achievements_total
            : null;

        return [
            'id' => $gameId,
            'database_id' => null,
            'slug' => null,
            'public_url' => null,

            'custom_game_id' => $customGame->id,
            'appid' => null,

            'title' => $customGame->title,
            'publisher' => $customGame->publisher,
            'developer' => $customGame->developer,

            'cover_url' => $customGame->cover_url,
            'header_image' => $customGame->header_image_url ?: $customGame->cover_url,
            'header_image_url' => $customGame->header_image_url,

            'description' => $customGame->description,
            'about' => $customGame->description,

            'developers' => $customGame->developer ? [$customGame->developer] : [],
            'publishers' => $customGame->publisher ? [$customGame->publisher] : [],

            'genres' => [],
            'screenshots' => [],
            'platforms' => [],

            'release_date' => $customGame->release_date
                ? $customGame->release_date->translatedFormat('M j, Y')
                : null,

            'steam_url' => null,
            'igdb_id' => $customGame->igdb_id,
            'igdb_slug' => $customGame->igdb_slug,
            'igdb_url' => $customGame->igdb_url,
            'platform' => $customGame->platform,

            'playtime_forever' => (int) ($customGame->playtime_minutes ?? 0),
            'playtime_hours' => $customGame->playtime_minutes !== null
                ? round($customGame->playtime_minutes / 60, 1)
                : null,

            'achievements_unlocked' => $achievementsUnlocked,
            'achievements_total' => $achievementsTotal,
            'achievement_percent' => $achievementsTotal
                ? (int) round(($achievementsUnlocked / max($achievementsTotal, 1)) * 100)
                : 0,

            'is_custom' => true,
            'source' => $customGame->source ?? 'manual',

            'suggested_game' => null,

            ...$this->meta->metaPayload($meta),
        ];
    }

    public function steamGameDetails(string $gameId, SteamService $steam): array
    {
        $user = Auth::user();
        $steamId = (string) $user->steam_id;

        $details = $this->cachedAppDetails($steam, $gameId);

        abort_if(! $details, 404);

        $canonicalGame = $this->findOrCreateCanonicalSteamGame($gameId, $details);

        $ownedGame = $this->ownedGame($steam, $steamId, $gameId);
        $achievements = $this->cachedAchievements($steam, $steamId, $gameId);
        $meta = $this->meta->metaFor($gameId);

        $achievementPercent = ! empty($achievements['total'])
            ? (int) round(((int) ($achievements['unlocked'] ?? 0) / max((int) $achievements['total'], 1)) * 100)
            : 0;

        $genres = $this->genreNames($details);

        $suggestedGame = $this->suggestedGame(
            $canonicalGame,
            $genres
        );

        return [
            'id' => $gameId,
            'database_id' => $canonicalGame->id,
            'slug' => $canonicalGame->slug,
            'public_url' => route('games.public.show', $canonicalGame),

            'appid' => $gameId,
            'steam_app_id' => $gameId,

            'title' => $details['name'] ?? $canonicalGame->title ?? 'Unknown game',
            'publisher' => $details['publishers'][0] ?? null,
            'developer' => $details['developers'][0] ?? null,

            'cover_url' => $details['capsule_imagev5']
                ?? $details['header_image']
                ?? $canonicalGame->cover_url
                ?? $this->steamCoverUrl($gameId),

            'header_image' => $details['header_image'] ?? $canonicalGame->header_image_url,
            'header_image_url' => $details['header_image'] ?? $canonicalGame->header_image_url,

            'description' => $this->plainTextFromHtml($details['short_description'] ?? ''),
            'about' => $this->plainTextFromHtml($details['about_the_game'] ?? ''),

            'developers' => $details['developers'] ?? [],
            'publishers' => $details['publishers'] ?? [],
            'genres' => $genres,
            'screenshots' => $this->screenshotUrls($details),
            'platforms' => $details['platforms'] ?? [],

            'release_date' => $details['release_date']['date'] ?? null,
            'steam_url' => "https://store.steampowered.com/app/{$gameId}",
            'igdb_url' => null,

            'playtime_hours' => $this->playtimeHours($ownedGame),
            'achievements_unlocked' => $achievements['unlocked'] ?? null,
            'achievements_total' => $achievements['total'] ?? null,
            'achievement_percent' => $achievementPercent,

            'is_custom' => false,
            'source' => 'steam',

            'suggested_game' => $suggestedGame,

            ...$this->meta->metaPayload($meta),
        ];
    }

    private function suggestedGame(
        Game $currentGame,
        array $genres
    ): ?array {
        if (empty($genres)) {
            return null;
        }

        $matchedGenre = collect($genres)->random();

        $suggestedGame = Game::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereKeyNot($currentGame->id)
            ->inRandomOrder()
            ->first();

        if (! $suggestedGame) {
            return null;
        }

        return [
            'title' => $suggestedGame->title,

            'slug' => $suggestedGame->slug,

            'cover_url' => $suggestedGame->steam_app_id
                ? $this->steamLibraryCoverUrl(
                    (string) $suggestedGame->steam_app_id
                )
                : (
                    $suggestedGame->cover_url
                    ?? $suggestedGame->header_image_url
                ),

            'matched_genre' => $matchedGenre,
        ];
    }

    private function findOrCreateCanonicalSteamGame(
        string $steamAppId,
        array $details
    ): Game {
        $title = $details['name'] ?? 'Unknown game';
        $normalizedTitle = GameTitleNormalizer::normalize($title);

        $game = Game::query()
            ->where('steam_app_id', $steamAppId)
            ->first();

        if (! $game) {
            $game = Game::query()
                ->whereNull('steam_app_id')
                ->where('normalized_title', $normalizedTitle)
                ->orderByRaw("case when source = 'igdb' then 0 else 1 end")
                ->first();
        }

        if (! $game) {
            $game = new Game();
        }

        $shortDescription = $this->plainTextFromHtml(
            $details['short_description'] ?? ''
        );

        $game->fill([
            'steam_app_id' => $steamAppId,
            'title' => $game->title ?: $title,
            'normalized_title' => $game->normalized_title ?: $normalizedTitle,
            'summary' => $game->summary ?: $shortDescription,
            'source' => $game->source === 'igdb' ? 'merged' : 'steam',

            'cover_url' => $details['capsule_imagev5']
                ?? $details['header_image']
                ?? $game->cover_url,

            'header_image_url' => $details['header_image']
                ?? $game->header_image_url,
        ]);

        if (! $game->slug) {
            $game->slug = $this->uniqueSlug($title, $game->id);
        }

        $game->save();

        return $game;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);

        if (blank($baseSlug)) {
            $baseSlug = 'game';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Game::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function plainTextFromHtml(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $html = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\s*\/p\s*>/i', "\n\n", $html);
        $html = preg_replace('/<\s*\/div\s*>/i', "\n\n", $html);
        $html = preg_replace('/<\s*\/li\s*>/i', "\n", $html);
        $html = preg_replace('/<\s*li[^>]*>/i', '• ', $html);
        $html = preg_replace('/<\s*\/h[1-6]\s*>/i', "\n\n", $html);

        $text = strip_tags($html);

        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }

    private function cachedAppDetails(SteamService $steam, string $gameId): ?array
    {
        return $this->appDetailsCache[$gameId]
            ??= $steam->getAppDetails($gameId);
    }

    private function ownedGame(
        SteamService $steam,
        string $steamId,
        string $gameId
    ): ?array {
        $games = $this->ownedGamesCache[$steamId]
            ??= collect($steam->getOwnedGames($steamId))
                ->keyBy(fn (array $game) => (string) $game['appid'])
                ->all();

        return $games[(string) $gameId] ?? null;
    }

    private function cachedAchievements(
        SteamService $steam,
        string $steamId,
        string $gameId
    ): array {
        $cacheKey = "{$steamId}:{$gameId}";

        return $this->achievementsCache[$cacheKey]
            ??= $steam->getPlayerAchievements($steamId, $gameId);
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

    private function steamLibraryCoverUrl(string $gameId): string
    {
        return "https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/{$gameId}/library_600x900.jpg";
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