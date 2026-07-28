<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\TierList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TierListTest extends TestCase
{
    use RefreshDatabase;

    public function test_tier_list_maker_is_public(): void
    {
        $this->get(route('tier-lists.maker'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tier-lists/editor')
                ->where('tierList', null));
    }

    public function test_tier_list_gallery_is_public_and_hides_private_lists(): void
    {
        $user = User::factory()->create();
        $board = [
            'tiers' => [
                ['id' => 's', 'name' => 'S', 'color' => '#ff7f7f'],
                ['id' => 'a', 'name' => 'A', 'color' => '#ffbf7f'],
            ],
            'items' => [],
        ];

        TierList::query()->create([
            'user_id' => $user->id,
            'title' => 'Public ranking',
            'slug' => 'public-ranking',
            'is_public' => true,
            'data' => $board,
        ]);

        TierList::query()->create([
            'user_id' => $user->id,
            'title' => 'Private ranking',
            'slug' => 'private-ranking',
            'is_public' => false,
            'data' => $board,
        ]);

        $this->get(route('tier-lists.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tier-lists/index')
                ->has('tierLists.data', 1)
                ->where('tierLists.data.0.title', 'Public ranking')
                ->where('tierLists.data.0.is_owner', false));

        $this->actingAs($user)
            ->get(route('tier-lists.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('tierLists.data', 1)
                ->where('tierLists.data.0.title', 'Public ranking'));
    }

    public function test_home_shows_only_public_community_tier_lists(): void
    {
        $user = User::factory()->create();
        $board = [
            'tiers' => [
                ['id' => 's', 'name' => 'S', 'color' => '#ff7f7f'],
                ['id' => 'a', 'name' => 'A', 'color' => '#ffbf7f'],
            ],
            'items' => [],
        ];

        foreach ([true, false] as $isPublic) {
            TierList::query()->create([
                'user_id' => $user->id,
                'title' => $isPublic ? 'Community ranking' : 'Hidden draft',
                'slug' => $isPublic ? 'community-ranking' : 'hidden-draft',
                'is_public' => $isPublic,
                'data' => $board,
                'published_at' => $isPublic ? now() : null,
            ]);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('home')
                ->has('tierLists', 1)
                ->where('tierLists.0.title', 'Community ranking'));
    }

    public function test_public_tier_list_gallery_is_paginated(): void
    {
        $user = User::factory()->create();
        $board = [
            'tiers' => [
                ['id' => 's', 'name' => 'S', 'color' => '#ff7f7f'],
                ['id' => 'a', 'name' => 'A', 'color' => '#ffbf7f'],
            ],
            'items' => [],
        ];

        foreach (range(1, 13) as $number) {
            TierList::query()->create([
                'user_id' => $user->id,
                'title' => "Ranking {$number}",
                'slug' => "ranking-{$number}",
                'is_public' => true,
                'data' => $board,
                'published_at' => now(),
            ]);
        }

        $this->get(route('tier-lists.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('tierLists.data', 12)
                ->where('tierLists.total', 13)
                ->where('tierLists.last_page', 2));
    }

    public function test_authenticated_user_can_save_and_share_a_tier_list(): void
    {
        $user = User::factory()->create();
        $game = Game::query()->create([
            'title' => 'Celeste',
            'slug' => 'celeste',
            'genres' => [],
        ]);

        $response = $this->actingAs($user)->post(route('tier-lists.store'), [
            'title' => 'Platformers ranked',
            'description' => 'My platformer ranking.',
            'is_public' => true,
            'tiers' => [
                ['id' => 's', 'name' => 'S', 'color' => '#ff7f7f'],
                ['id' => 'a', 'name' => 'A', 'color' => '#ffbf7f'],
            ],
            'items' => [
                ['id' => $game->id, 'tier_id' => 's', 'position' => 0],
            ],
        ]);

        $tierList = TierList::query()->firstOrFail();

        $response->assertRedirect(route('tier-lists.edit', $tierList));
        $this->assertTrue($tierList->is_public);
        $this->assertSame('Celeste', $tierList->data['items'][0]['title']);

        auth()->logout();

        $this->get(route('tier-lists.public.show', $tierList))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tier-lists/show')
                ->where('tierList.title', 'Platformers ranked')
                ->where('tierList.items.0.title', 'Celeste')
                ->where('isOwner', false));
    }

    public function test_private_tier_list_is_hidden_from_guests(): void
    {
        $tierList = TierList::query()->create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Private ranking',
            'slug' => 'private-ranking',
            'is_public' => false,
            'data' => [
                'tiers' => [
                    ['id' => 's', 'name' => 'S', 'color' => '#ff7f7f'],
                    ['id' => 'a', 'name' => 'A', 'color' => '#ffbf7f'],
                ],
                'items' => [],
            ],
        ]);

        $this->get(route('tier-lists.public.show', $tierList))
            ->assertNotFound();
    }
}
