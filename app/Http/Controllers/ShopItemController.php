<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\Challenge;
use App\Models\ChallengeSubmission;
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

        $shopItems = ShopItem::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (ShopItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'type' => $item->type,
            ]);

        $challenges = Challenge::query()
            ->with('item')
            ->latest()
            ->get()
            ->map(fn (Challenge $challenge) => [
                'id' => $challenge->id,
                'title' => $challenge->title,
                'description' => $challenge->description,
                'game_name' => $challenge->game_name,
                'reward_xp' => $challenge->reward_xp,
                'reward_coins' => $challenge->reward_coins,
                'is_active' => $challenge->is_active,

                'item' => $challenge->item ? [
                    'id' => $challenge->item->id,
                    'name' => $challenge->item->name,
                    'type' => $challenge->item->type,
                ] : null,
            ]);

        $submissions = ChallengeSubmission::query()
            ->with(['challenge.item', 'user'])
            ->latest()
            ->get()
            ->map(fn (ChallengeSubmission $submission) => [
                'id' => $submission->id,
                'status' => $submission->status,
                'admin_note' => $submission->admin_note,
                'created_at' => $submission->created_at?->format('Y-m-d H:i'),
                'reviewed_at' => $submission->reviewed_at?->format('Y-m-d H:i'),
                'screenshot_url' => Storage::url($submission->screenshot_path),

                'user' => [
                    'id' => $submission->user->id,
                    'name' => $submission->user->name,
                    'email' => $submission->user->email,
                    'avatar' => $submission->user->steam_avatar_url,
                ],

                'challenge' => [
                    'id' => $submission->challenge->id,
                    'title' => $submission->challenge->title,
                    'game_name' => $submission->challenge->game_name,
                    'reward_xp' => $submission->challenge->reward_xp,
                    'reward_coins' => $submission->challenge->reward_coins,

                    'item' => $submission->challenge->item ? [
                        'id' => $submission->challenge->item->id,
                        'name' => $submission->challenge->item->name,
                    ] : null,
                ],
            ]);

        return Inertia::render('admin/index', [
            ...Payload::pageData($steam),

            'items' => $items,
            'shopItems' => $shopItems,
            'challenges' => $challenges,
            'submissions' => $submissions,
            'reviewReports' => Payload::reviewReports(),
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