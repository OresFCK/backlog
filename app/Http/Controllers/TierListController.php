<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTierListRequest;
use App\Models\Game;
use App\Models\TierList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TierListController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('tier-lists/create');
    }

    public function store(StoreTierListRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tierIds = collect($data['tiers'])->pluck('id');
        $games = Game::query()
            ->whereIntegerInRaw(
                'id',
                collect($data['items'])
                    ->pluck('game_id')
                    ->map(fn ($id) => (int) $id)
                    ->all()
            )
            ->get()
            ->keyBy('id');

        $items = collect($data['items'])
            ->map(function (array $item) use ($tierIds, $games) {
                $game = $games->get((int) $item['game_id']);
                $item['tier_id'] = $tierIds->contains($item['tier_id'] ?? null)
                    ? $item['tier_id']
                    : null;
                $item['title'] = $game->title;
                $item['slug'] = $game->slug;
                $item['image_url'] = $game->header_image_url
                    ?: (filled($game->steam_app_id)
                        ? "https://cdn.cloudflare.steamstatic.com/steam/apps/{$game->steam_app_id}/header.jpg"
                        : null)
                    ?: $game->cover_url
                    ?: $game->igdb_cover_url;

                return $item;
            })
            ->values()
            ->all();

        $tierList = TierList::query()->create([
            'user_id' => $request->user()?->id,
            'slug' => Str::lower(Str::random(12)),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'tiers' => array_values($data['tiers']),
            'items' => $items,
            'is_public' => true,
        ]);

        return to_route('tier-lists.show', $tierList);
    }

    public function show(TierList $tierList): Response
    {
        abort_unless($tierList->is_public, 404);

        return Inertia::render('tier-lists/show', [
            'tierList' => $this->payload($tierList),
            'seo' => [
                'title' => $tierList->title.' | Curator.gg Tier List',
                'description' => $tierList->description
                    ?: 'See this community game tier list and create your own version on Curator.gg.',
                'url' => route('tier-lists.show', $tierList),
                'image' => asset('og-image.jpg'),
                'image_alt' => $tierList->title,
            ],
        ]);
    }

    public function make(TierList $tierList): Response
    {
        abort_unless($tierList->is_public, 404);

        $payload = $this->payload($tierList);
        $payload['items'] = collect($payload['items'])
            ->map(fn (array $item) => [...$item, 'tier_id' => null])
            ->values()
            ->all();

        return Inertia::render('tier-lists/create', [
            'template' => $payload,
        ]);
    }

    public function mine(): Response
    {
        $tierLists = TierList::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(fn (TierList $tierList) => $this->payload($tierList))
            ->values();

        return Inertia::render('tier-lists/mine', [
            'tierLists' => $tierLists,
        ]);
    }

    private function payload(TierList $tierList): array
    {
        return [
            'id' => $tierList->id,
            'slug' => $tierList->slug,
            'title' => $tierList->title,
            'description' => $tierList->description,
            'tiers' => $tierList->tiers,
            'items' => $tierList->items,
            'result_url' => route('tier-lists.show', $tierList),
            'template_url' => route('tier-lists.make', $tierList),
            'created_at' => $tierList->created_at?->diffForHumans(),
        ];
    }
}
