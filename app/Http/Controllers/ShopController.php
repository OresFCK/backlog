<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\ShopItem;
use App\Models\UserShopItem;
use App\Services\SteamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request, SteamService $steam): Response
    {
        $user = $request->user();

        $ownedItemIds = UserShopItem::where('user_id', $user->id)
            ->pluck('shop_item_id');

        $items = ShopItem::query()
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('price')
            ->get()
            ->map(function (ShopItem $item) use ($user, $ownedItemIds) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'type' => $item->type,
                    'price' => $item->price,
                    'preview_url' => $item->preview_url,

                    'owned' => $ownedItemIds->contains($item->id),

                    'equipped' => UserShopItem::where('user_id', $user->id)
                        ->where('shop_item_id', $item->id)
                        ->where('is_equipped', true)
                        ->exists(),
                ];
            });

        return Inertia::render('shop/index', [
            ...Payload::pageData($steam),

            'items' => $items,
        ]);
    }

    public function buy(Request $request, ShopItem $item): RedirectResponse
    {
        $user = $request->user();

        UserShopItem::firstOrCreate([
            'user_id' => $user->id,
            'shop_item_id' => $item->id,
        ]);

        return back();
    }

    public function equip(Request $request, ShopItem $item): RedirectResponse
    {
        $user = $request->user();

        $this->unequipItemsOfSameType($user->id, $item->type);

        UserShopItem::updateOrCreate(
            [
                'user_id' => $user->id,
                'shop_item_id' => $item->id,
            ],
            [
                'is_equipped' => true,
            ]
        );

        return back();
    }

    private function unequipItemsOfSameType(int $userId, string $type): void
    {
        UserShopItem::where('user_id', $userId)
            ->whereHas('item', function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->update([
                'is_equipped' => false,
            ]);
    }
}