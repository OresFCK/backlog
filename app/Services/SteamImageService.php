<?php

namespace App\Services;

class SteamImageService
{
    public function appIdFromUrls(?string ...$urls): ?int
    {
        foreach ($urls as $url) {
            if (
                filled($url)
                && preg_match('~/steam/apps/([1-9][0-9]*)/~', $url, $matches)
            ) {
                return (int) $matches[1];
            }
        }

        return null;
    }

    public function coverUrl(int $appId): string
    {
        return "https://cdn.cloudflare.steamstatic.com/steam/apps/{$appId}/library_600x900.jpg";
    }

    public function headerUrl(int $appId): string
    {
        return "https://cdn.cloudflare.steamstatic.com/steam/apps/{$appId}/library_hero.jpg";
    }
}
