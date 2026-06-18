<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\CustomList;
use App\Models\CustomListItem;
use App\Services\GameLibraryService;
use App\Services\SteamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CustomListController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('lists/index', [
            'user' => Payload::currentUser(),

            'lists' => CustomList::query()
                ->where('user_id', Auth::id())
                ->withCount('items')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['required', 'in:public,friends,private'],
        ]);

        $baseSlug = Str::slug($data['title']) ?: 'list';
        $slug = $baseSlug;
        $counter = 2;

        while (
            CustomList::query()
                ->where('user_id', Auth::id())
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $list = CustomList::query()->create([
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'visibility' => $data['visibility'],
        ]);

        return redirect()->route('lists.show', $list);
    }

    public function show(
        CustomList $list,
        SteamService $steam,
        GameLibraryService $library
    ): Response {
        abort_if($list->user_id !== Auth::id(), 403);

        $libraryGames = collect($library->allGames($steam))
            ->map(fn ($game) => [
                'id' => (string) (
                    $game['appid']
                    ?? $game['steam_app_id']
                    ?? $game['id']
                    ?? $game['database_id']
                ),

                'title' => $game['title']
                    ?? $game['name']
                    ?? 'Unknown game',

                'slug' => $game['slug'] ?? null,

                'cover_url' => $game['cover_url']
                    ?? (
                        isset($game['appid'])
                            ? "https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/{$game['appid']}/library_600x900.jpg"
                            : null
                    ),

                'header_image_url' => $game['header_image_url'] ?? null,

                'steam_app_id' => isset($game['appid'])
                    ? (string) $game['appid']
                    : (
                        isset($game['steam_app_id'])
                            ? (string) $game['steam_app_id']
                            : null
                    ),
            ])
            ->filter(fn ($game) => filled($game['id']))
            ->values()
            ->all();

        return Inertia::render('lists/show', [
            'user' => Payload::currentUser(),
            'list' => $list->load('items'),
            'games' => $libraryGames,
        ]);
    }

    public function storeItem(
        Request $request,
        CustomList $list
    ): RedirectResponse {
        abort_if($list->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'game_id' => ['required', 'string'],
            'game_title' => ['required', 'string', 'max:255'],
            'game_cover_url' => ['nullable', 'string', 'max:1000'],
            'game_slug' => ['nullable', 'string', 'max:255'],
            'steam_app_id' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        CustomListItem::query()->updateOrCreate(
            [
                'custom_list_id' => $list->id,
                'game_id' => $data['game_id'],
            ],
            [
                'game_title' => $data['game_title'],
                'game_cover_url' => $data['game_cover_url'] ?? null,
                'game_slug' => $data['game_slug'] ?? null,
                'steam_app_id' => $data['steam_app_id'] ?? null,
                'note' => $data['note'] ?? null,
                'position' => ((int) $list->items()->max('position')) + 1,
            ]
        );

        return back();
    }

    public function reorder(
        Request $request,
        CustomList $list
    ): RedirectResponse {
        abort_if($list->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:custom_list_items,id'],
            'items.*.position' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($data['items'] as $item) {
            CustomListItem::query()
                ->where('custom_list_id', $list->id)
                ->whereKey($item['id'])
                ->update([
                    'position' => $item['position'],
                ]);
        }

        return back();
    }

    public function destroyItem(
        CustomList $list,
        CustomListItem $item
    ): RedirectResponse {
        abort_if($list->user_id !== Auth::id(), 403);
        abort_if($item->custom_list_id !== $list->id, 403);

        $item->delete();

        return back();
    }
}