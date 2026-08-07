<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SharedRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_recommendation_card_is_public_and_keeps_game_order(): void
    {
        $first = Game::query()->create([
            'title' => 'First Pick',
            'slug' => 'first-pick',
            'steam_app_id' => 10,
        ]);
        $second = Game::query()->create([
            'title' => 'Second Pick',
            'slug' => 'second-pick',
            'steam_app_id' => 20,
        ]);

        $this->get(route('recommendations.shared', [
            'games' => "{$second->id},{$first->id}",
            'mood' => 'immersive',
        ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('recommendations/shared')
                ->where('mood', 'immersive')
                ->where('games.0.id', $second->id)
                ->where('games.1.id', $first->id)
                ->has('games', 2));
    }

    public function test_shared_recommendation_card_rejects_an_empty_game_list(): void
    {
        $this->get('/shared/recommendations?games=invalid')->assertNotFound();
    }
}
