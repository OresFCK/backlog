<?php

namespace App\Helpers;

class CacheKeys
{
    public static function userBase(int $userId): string
    {
        return "user:$userId:base";
    }

    public static function userMeta(int $userId, string $gameId): string
    {
        return "user:$userId:meta:$gameId";
    }

    public static function userMetas(int $userId): string
    {
        return "user:$userId:metas";
    }

    public static function userStatuses(int $userId): string
    {
        return "user:$userId:statuses";
    }

    public static function userStatusColors(int $userId): string
    {
        return "user:$userId:status-colors";
    }

    public static function userCustomLabels(int $userId): string
    {
        return "user:$userId:custom-labels";
    }

    public static function userOwnedGameIds(int $userId): string
    {
        return "user:$userId:owned-game-ids";
    }

    public static function userFriendIds(int $userId): string
    {
        return "user:$userId:friend-ids";
    }

    public static function userRecommendations(int $userId): string
    {
        return "user:$userId:recommendations";
    }

    public static function userLibrary(int $userId): string
    {
        return "user:$userId:library:all";
    }

    public static function userLibraryForUser(int $userId): string
    {
        return "user:$userId:library:for-user";
    }

    public static function userLibraryStatus(
        int $userId,
        string $status
    ): string {
        return "user:$userId:library:status:" . strtolower($status);
    }

    public static function userWishlist(int $userId): string
    {
        return "user:$userId:library:wishlist";
    }

    public static function userActivity(int $userId): string
    {
        return "user:$userId:activity";
    }

    public static function userEquippedItems(int $userId): string
    {
        return "user:$userId:equipped-items";
    }

    public static function publicProfile(int $userId): string
    {
        return "public-profile:$userId";
    }

    public static function profilePage(int $userId): string
    {
        return "profile-page:$userId";
    }

    public static function featuredGames(int $userId): string
    {
        return "user:$userId:featured-games";
    }

    public static function featuredReviews(int $userId): string
    {
        return "user:$userId:featured-reviews";
    }

    public static function featuredWardrobeItems(int $userId): string
    {
        return "user:$userId:featured-wardrobe-items";
    }

    public static function reviewReports(): string
    {
        return 'admin:review-reports';
    }

    public static function gameDetails(string $gameId): string
    {
        return "game:$gameId:details";
    }

    public static function steamOwnedGames(string $steamId): string
    {
        return "steam:$steamId:owned-games";
    }

    public static function steamOwnedGame(
        string $steamId,
        string $appId
    ): string {
        return "steam:$steamId:owned-game:$appId";
    }

    public static function steamAchievements(
        string $steamId,
        string $appId
    ): string {
        return "steam:$steamId:achievements:$appId";
    }

    public static function steamPlayerSummary(
        string $steamId
    ): string {
        return "steam:$steamId:player-summary";
    }

    public static function steamVanity(
        string $vanity
    ): string {
        return 'steam:vanity:' . md5(
            strtolower(trim($vanity))
        );
    }

    public static function steamSearch(
        string $query
    ): string {
        return 'steam:search:' . md5(
            strtolower(trim($query))
        );
    }

    public static function steamAppDetails(
        string $appId
    ): string {
        return "steam:app:$appId:details";
    }

    public static function igdbToken(): string
    {
        return 'igdb:token';
    }

    public static function igdbDump(
        string $endpoint
    ): string {
        return "igdb:dump:$endpoint";
    }

    public static function igdbDumps(): string
    {
        return 'igdb:dumps';
    }
}