<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Http\Controllers\Controller;
use App\Models\ShopItem;
use App\Services\SteamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ShopItemController extends Controller
{
    public function index(SteamService $steam): Response
    {
        $items = ShopItem::query()
            ->latest()
            ->get()
            ->map(fn (ShopItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'type' => $item->type,
                'price' => $item->price,
                'is_active' => $item->is_active,

                'image_url' => $item->image_path
                    ? Storage::url($item->image_path)
                    : null,
            ]);

        return Inertia::render('admin/index', [
            ...Payload::pageData($steam),

            'items' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request
                ->file('image')
                ->store('shop-items', 'public');
        }

        unset($data['image']);

        ShopItem::create([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back();
    }

    public function update(Request $request, ShopItem $item): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }

            $data['image_path'] = $request
                ->file('image')
                ->store('shop-items', 'public');
        }

        unset($data['image']);

        $item->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back();
    }

    public function destroy(ShopItem $item): RedirectResponse
    {
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return back();
    }
}