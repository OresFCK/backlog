<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\ShopItem;
use App\Models\UserShopItem;
use App\Services\SteamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
                $userItem = UserShopItem::where('user_id', $user->id)
                    ->where('shop_item_id', $item->id)
                    ->first();

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'type' => $item->type,
                    'price' => $item->price,
                    'image_url' => $item->image_path
                        ? Storage::url($item->image_path)
                        : null,
                    'owned' => $ownedItemIds->contains($item->id),
                    'equipped' => (bool) $userItem?->is_equipped,
                ];
            });

        return Inertia::render('shop/index', [
            ...Payload::pageData($steam),
            'items' => $items,
        ]);
    }

    public function buy(Request $request, ShopItem $item): RedirectResponse
    {
        $user = $request->user()->fresh();

        if (! $item->is_active) {
            return back()->with('error', 'This item is not available.');
        }

        if (
            UserShopItem::where('user_id', $user->id)
                ->where('shop_item_id', $item->id)
                ->exists()
        ) {
            return back()->with('error', 'You already own this item.');
        }

        if ($user->coins < $item->price) {
            return back()->with('error', 'Not enough coins.');
        }

        DB::transaction(function () use ($user, $item) {
            $user->decrement('coins', $item->price);

            UserShopItem::create([
                'user_id' => $user->id,
                'shop_item_id' => $item->id,
            ]);
        });

        return back()->with('success', 'Item purchased successfully.');
    }

    public function equip(Request $request, ShopItem $item): RedirectResponse
    {
        $user = $request->user();

        $userItem = UserShopItem::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->first();

        if (! $userItem) {
            return back()->with('error', 'You do not own this item.');
        }

        $this->unequipItemsOfSameType($user->id, $item->type);

        $userItem->update([
            'is_equipped' => true,
        ]);

        return back()->with('success', 'Item equipped successfully.');
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