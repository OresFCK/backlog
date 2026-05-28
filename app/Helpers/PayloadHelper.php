<?php

namespace App\Helpers;

use App\Http\Requests\StoreCustomGameRequest;
use App\Http\Requests\StoreCustomLabelRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use App\Http\Requests\UpdateGameMetaRequest;

use App\Models\UserShopItem;

use App\Services\GameDetailsService;
use App\Services\GameLibraryService;
use App\Services\GameMetaService;
use App\Services\StatusService;
use App\Services\SteamService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PayloadHelper
{
    public static function pageData(
        SteamService $steam
    ): array {

        return [
            'user' => self::currentUser(),

            'games' => self::library()
                ->allGames($steam),

            'statuses' => self::statuses(),
        ];
    }

    public static function backlogPageData(
        SteamService $steam
    ): array {

        return [
            ...self::pageData($steam),

            'games' => self::library()
                ->gamesByStatus(
                    $steam,
                    'Backlog'
                ),
        ];
    }

    public static function playingPageData(
        SteamService $steam
    ): array {

        return [
            ...self::pageData($steam),

            'games' => self::library()
                ->gamesByStatus(
                    $steam,
                    'Playing'
                ),
        ];
    }

    public static function finishedPageData(
        SteamService $steam
    ): array {

        return [
            ...self::pageData($steam),

            'games' => self::library()
                ->gamesByStatus(
                    $steam,
                    'Finished'
                ),
        ];
    }

    public static function droppedPageData(
        SteamService $steam
    ): array {

        return [
            ...self::pageData($steam),

            'games' => self::library()
                ->gamesByStatus(
                    $steam,
                    'Dropped'
                ),
        ];
    }

    public static function wishlistPageData(
        SteamService $steam
    ): array {

        return [
            ...self::pageData($steam),

            'games' => self::library()
                ->wishlistGames($steam),
        ];
    }

    public static function profilePageData(
        SteamService $steam
    ): array {

        return [
            'user' => self::currentUser(),

            'games' => self::library()
                ->allGames($steam),

            'activity' => self::library()
                ->activityLog($steam),

            'equippedItems' => self::equippedItems(),
        ];
    }

    public static function gamePageData(
        string $gameId,
        SteamService $steam
    ): array {

        return [
            'user' => self::currentUser(),

            'statuses' => self::statuses(),

            'game' => self::details()
                ->gameDetails(
                    $gameId,
                    $steam
                ),
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

            'is_admin' => $user->is_admin,
        ];
    }

    public static function equippedItems(): array
    {
        return UserShopItem::query()

            ->with('item')

            ->where(
                'user_id',
                Auth::id()
            )

            ->where(
                'is_equipped',
                true
            )

            ->get()

            ->map(fn ($ownedItem) => [

                'id' =>
                    $ownedItem->item->id,

                'name' =>
                    $ownedItem->item->name,

                'type' =>
                    $ownedItem->item->type,

                'image_url' =>
                    $ownedItem->item->image_path
                        ? Storage::url(
                            $ownedItem->item->image_path
                        )
                        : null,
            ])

            ->values()

            ->toArray();
    }

    public static function statuses(): array
    {
        return self::status()->statuses();
    }

    public static function customLabels(): array
    {
        return self::status()->customLabels();
    }

    public static function storeCustomLabel(
        StoreCustomLabelRequest $request
    ): RedirectResponse {

        return self::status()
            ->storeCustomLabel($request);
    }

    public static function storeStatus(
        StoreCustomStatusRequest $request
    ): RedirectResponse {

        return self::status()
            ->storeStatus($request);
    }

    public static function storeCustomGame(
        StoreCustomGameRequest $request
    ): RedirectResponse {

        return self::library()
            ->storeCustomGame($request);
    }

    public static function storeMeta(
        UpdateGameMetaRequest $request,
        string $gameId
    ): RedirectResponse {

        return self::meta()
            ->storeMeta(
                $request,
                $gameId
            );
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

    private static function library(): GameLibraryService
    {
        return app(GameLibraryService::class);
    }

    private static function details(): GameDetailsService
    {
        return app(GameDetailsService::class);
    }

    private static function meta(): GameMetaService
    {
        return app(GameMetaService::class);
    }

    private static function status(): StatusService
    {
        return app(StatusService::class);
    }
}