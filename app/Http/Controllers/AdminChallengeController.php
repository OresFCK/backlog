<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ShopItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminChallengeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/challenges', [
            'challenges' => Challenge::with('item')->latest()->get(),
            'shopItems' => ShopItem::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Challenge::create($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'reward_xp' => ['required', 'integer', 'min:0'],
            'reward_coins' => ['required', 'integer', 'min:0'],
            'shop_item_id' => ['nullable', 'exists:shop_items,id'],
            'is_active' => ['boolean'],
        ]));

        return back();
    }

    public function destroy(Challenge $challenge): RedirectResponse
    {
        $challenge->delete();

        return back();
    }
}