<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\Game;
use App\Models\TierList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TierListController extends Controller
{
    public function maker(): Response
    {
        return Inertia::render('tier-lists/editor', [
            'user' => Auth::check() ? Payload::currentUser() : null,
            'tierList' => null,
            'seo' => [
                'title' => 'Game Tier List Maker | Curator.gg',
                'description' => 'Create a free game tier list, rank your favorite games and share the finished list with friends.',
                'url' => route('tier-lists.maker'),
                'image' => asset('og-image.jpg'),
                'image_alt' => 'Curator.gg game tier list maker',
            ],
        ]);
    }

    public function index(): Response
    {
        return Inertia::render('tier-lists/index', [
            'user' => Auth::check() ? Payload::currentUser() : null,
            'tierLists' => TierList::query()
                ->where('is_public', true)
                ->with('user')
                ->latest('updated_at')
                ->paginate(12)
                ->withQueryString()
                ->through(fn (TierList $tierList) => $this->summary($tierList)),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $tierList = TierList::query()->create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'description' => $validated['description'] ?? null,
            'is_public' => $validated['is_public'],
            'data' => $this->normalizedBoard($validated),
            'published_at' => $validated['is_public'] ? now() : null,
        ]);

        return redirect()
            ->route('tier-lists.edit', $tierList)
            ->with('success', 'Tier list saved.');
    }

    public function edit(TierList $tierList): Response
    {
        $this->authorizeOwner($tierList);

        return Inertia::render('tier-lists/editor', [
            'user' => Payload::currentUser(),
            'tierList' => $this->payload($tierList),
        ]);
    }

    public function update(Request $request, TierList $tierList): RedirectResponse
    {
        $this->authorizeOwner($tierList);
        $validated = $this->validatedData($request);

        $tierList->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_public' => $validated['is_public'],
            'data' => $this->normalizedBoard($validated),
            'published_at' => $validated['is_public']
                ? ($tierList->published_at ?? now())
                : null,
        ]);

        return back()->with('success', 'Tier list saved.');
    }

    public function show(TierList $tierList): Response
    {
        abort_unless(
            $tierList->is_public || $tierList->user_id === Auth::id(),
            404
        );

        $tierList->loadMissing('user');
        $description = filled($tierList->description)
            ? Str::limit(Str::squish(strip_tags($tierList->description)), 155)
            : "See {$tierList->title}, a game tier list created on Curator.gg.";

        return Inertia::render('tier-lists/show', [
            'tierList' => $this->payload($tierList),
            'isOwner' => $tierList->user_id === Auth::id(),
            'seo' => [
                'title' => $tierList->title.' | Curator.gg Tier List',
                'description' => $description,
                'url' => route('tier-lists.public.show', $tierList),
                'image' => asset('og-image.jpg'),
                'image_alt' => $tierList->title,
            ],
        ]);
    }

    public function destroy(TierList $tierList): RedirectResponse
    {
        $this->authorizeOwner($tierList);
        $tierList->delete();

        return redirect()
            ->route('tier-lists.index')
            ->with('success', 'Tier list deleted.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['required', 'boolean'],
            'tiers' => ['required', 'array', 'min:2', 'max:12'],
            'tiers.*.id' => ['required', 'string', 'max:40', 'distinct'],
            'tiers.*.name' => ['required', 'string', 'max:40'],
            'tiers.*.color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'items' => ['present', 'array', 'max:200'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:games,id'],
            'items.*.tier_id' => ['nullable', 'string', 'max:40'],
            'items.*.position' => ['required', 'integer', 'min:0', 'max:200'],
        ]);

        $tierIds = collect($validated['tiers'])->pluck('id');
        $hasUnknownTier = collect($validated['items'])
            ->pluck('tier_id')
            ->filter()
            ->contains(fn (string $tierId) => ! $tierIds->contains($tierId));

        if ($hasUnknownTier) {
            throw ValidationException::withMessages([
                'items' => 'One or more games reference an unknown tier.',
            ]);
        }

        return $validated;
    }

    private function normalizedBoard(array $validated): array
    {
        $games = Game::query()
            ->whereKey(collect($validated['items'])->pluck('id'))
            ->get()
            ->keyBy('id');

        $items = collect($validated['items'])
            ->map(function (array $item) use ($games) {
                $game = $games->get($item['id']);

                return [
                    'id' => $game->id,
                    'title' => $game->title,
                    'slug' => $game->slug,
                    'cover_url' => $this->coverUrl($game),
                    'tier_id' => $item['tier_id'] ?? null,
                    'position' => $item['position'],
                ];
            })
            ->sortBy([
                ['tier_id', 'asc'],
                ['position', 'asc'],
            ])
            ->values()
            ->all();

        return [
            'tiers' => collect($validated['tiers'])
                ->values()
                ->map(fn (array $tier, int $position) => [
                    ...$tier,
                    'position' => $position,
                ])
                ->all(),
            'items' => $items,
        ];
    }

    private function payload(TierList $tierList): array
    {
        $tierList->loadMissing('user');

        return [
            'id' => $tierList->id,
            'title' => $tierList->title,
            'slug' => $tierList->slug,
            'description' => $tierList->description,
            'is_public' => $tierList->is_public,
            'published_at' => $tierList->published_at?->toAtomString(),
            'updated_at' => $tierList->updated_at?->toAtomString(),
            'tiers' => $tierList->data['tiers'] ?? [],
            'items' => $tierList->data['items'] ?? [],
            'share_url' => route('tier-lists.public.show', $tierList),
            'author' => [
                'name' => $tierList->user?->visible_name,
                'avatar' => $tierList->user?->steam_avatar_url,
                'profile_url' => filled($tierList->user?->steam_id)
                    ? route('profile.public', [
                        'user' => $tierList->user->steam_id,
                    ])
                    : null,
            ],
        ];
    }

    private function summary(TierList $tierList): array
    {
        return [
            ...$this->payload($tierList),
            'items_count' => count($tierList->data['items'] ?? []),
            'is_owner' => $tierList->user_id === Auth::id(),
        ];
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'tier-list';

        do {
            $slug = $base.'-'.Str::lower(Str::random(6));
        } while (TierList::query()->where('slug', $slug)->exists());

        return $slug;
    }

    private function authorizeOwner(TierList $tierList): void
    {
        abort_unless($tierList->user_id === Auth::id(), 403);
    }

    private function coverUrl(Game $game): ?string
    {
        $url = $game->cover_url
            ?: $game->igdb_cover_url
            ?: $game->header_image_url;

        return $url && str_starts_with($url, '//') ? 'https:'.$url : $url;
    }
}
