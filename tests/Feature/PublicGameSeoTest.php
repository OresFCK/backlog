<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\PublicReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicGameSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_game_page_exposes_game_specific_seo_metadata(): void
    {
        $game = Game::query()->create([
            'title' => 'Celeste',
            'slug' => 'celeste',
            'summary' => '<p>A challenging platformer about climbing a mountain.</p>',
            'header_image_url' => 'https://example.com/celeste-header.jpg',
            'genres' => [],
        ]);
        $user = User::factory()->create(['name' => 'Mountain Climber']);
        $review = PublicReview::query()->create([
            'user_id' => $user->id,
            'game_id' => (string) $game->id,
            'game_title' => $game->title,
            'title' => 'Worth the climb',
            'body' => 'Precise controls and a memorable story.',
            'rating' => 9,
            'recommended' => true,
            'is_public' => true,
        ]);

        $this->get(route('games.public.show', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('games/public-show')
                ->where(
                    'seo.title',
                    'Celeste Reviews, Ratings & Recommendations | Curator.gg'
                )
                ->where(
                    'seo.description',
                    'A challenging platformer about climbing a mountain.'
                )
                ->where(
                    'seo.url',
                    route('games.public.show', $game)
                )
                ->where(
                    'seo.image',
                    'https://example.com/celeste-header.jpg'
                )
                ->where(
                    'seo.image_alt',
                    'Celeste reviews and ratings'
                )
                ->where('seo.schema.@context', 'https://schema.org')
                ->where('seo.schema.@type', 'VideoGame')
                ->where('seo.schema.name', 'Celeste')
                ->where(
                    'seo.schema.url',
                    route('games.public.show', $game)
                )
                ->missing('seo.schema.genre')
                ->where('seo.schema.aggregateRating.ratingValue', 9)
                ->where('seo.schema.aggregateRating.ratingCount', 1)
                ->where('seo.schema.aggregateRating.bestRating', 10)
                ->where('seo.schema.review.0.@type', 'Review')
                ->where('seo.schema.review.0.name', 'Worth the climb')
                ->where(
                    'seo.schema.review.0.url',
                    route('reviews.public.show', $review)
                )
                ->where(
                    'seo.schema.review.0.author.name',
                    'Mountain Climber'
                ));
    }

    public function test_public_game_page_has_safe_seo_fallbacks(): void
    {
        $game = Game::query()->create([
            'title' => 'Unknown Adventure',
            'slug' => 'unknown-adventure',
            'genres' => [],
        ]);

        $this->get(route('games.public.show', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where(
                    'seo.description',
                    'Read 0 player reviews, ratings and recommendations for '
                        .'Unknown Adventure. See what the Curator.gg community thinks.'
                )
                ->where('seo.image', asset('og-image.jpg'))
                ->missing('seo.schema.aggregateRating')
                ->missing('seo.schema.review'));
    }
}
