<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class UserCache
{
    public static function flush(int $userId): void
    {
        Cache::forget(CacheKeys::userBase($userId));

        Cache::forget(CacheKeys::userMetas($userId));
        Cache::forget(CacheKeys::userStatuses($userId));
        Cache::forget(CacheKeys::userStatusColors($userId));
        Cache::forget(CacheKeys::userCustomLabels($userId));

        Cache::forget(CacheKeys::userOwnedGameIds($userId));
        Cache::forget(CacheKeys::userFriendIds($userId));
        Cache::forget(CacheKeys::userRecommendations($userId));

        Cache::forget(CacheKeys::userLibrary($userId));
        Cache::forget(CacheKeys::userLibraryForUser($userId));
        Cache::forget(CacheKeys::userWishlist($userId));
        Cache::forget(CacheKeys::userActivity($userId));
        Cache::forget(CacheKeys::userEquippedItems($userId));

        Cache::forget(CacheKeys::publicProfile($userId));
        Cache::forget(CacheKeys::profilePage($userId));

        Cache::forget(CacheKeys::featuredGames($userId));
        Cache::forget(CacheKeys::featuredReviews($userId));
        Cache::forget(CacheKeys::featuredWardrobeItems($userId));

        foreach (
            ['Backlog', 'Playing', 'Finished', 'Planned', 'Dropped']
            as $status
        ) {
            Cache::forget(
                CacheKeys::userLibraryStatus(
                    $userId,
                    $status
                )
            );
        }
    }
}