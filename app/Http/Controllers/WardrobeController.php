<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\ShopItem;
use App\Models\UserShopItem;
use App\Services\SteamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class WardrobeController extends Controller
{
    public function index(
        Request $request,
        SteamService $steam
    ): Response {
        $user = $request->user();

        $items = UserShopItem::query()
            ->with('item')
            ->where('user_id', $user->id)
            ->get()
            ->map(fn (UserShopItem $ownedItem) => [
                'id' => $ownedItem->item->id,
                'name' => $ownedItem->item->name,
                'description' => $ownedItem->item->description,
                'type' => $ownedItem->item->type,
                'price' => $ownedItem->item->price,
                'is_equipped' => $ownedItem->is_equipped,

                'image_url' => $ownedItem->item->image_path
                    ? Storage::url($ownedItem->item->image_path)
                    : null,
            ]);

        return Inertia::render('wardrobe/index', [
            ...Payload::pageData($steam),

            'items' => $items,
        ]);
    }

    public function equip(
        Request $request,
        ShopItem $item
    ): RedirectResponse {
        $user = $request->user();

        $ownsItem = UserShopItem::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->exists();

        if (! $ownsItem) {
            abort(403);
        }

        UserShopItem::where('user_id', $user->id)
            ->whereHas('item', fn ($query) =>
                $query->where('type', $item->type)
            )
            ->update([
                'is_equipped' => false,
            ]);

        UserShopItem::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->update([
                'is_equipped' => true,
            ]);

        return back();
    }

    public function unequip(
        Request $request,
        ShopItem $item
    ): RedirectResponse {
        $user = $request->user();

        UserShopItem::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->update([
                'is_equipped' => false,
            ]);

        return back();
    }
}