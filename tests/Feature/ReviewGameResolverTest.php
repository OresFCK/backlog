<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Services\ReviewGameResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReviewGameResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_source_id_is_not_queried_as_a_steam_app_id(): void
    {
        $game = Game::query()->create([
            'title' => 'Gothic 1 Remake',
            'normalized_title' => 'gothic 1 remake',
            'source' => 'manual',
        ]);

        $review = (object) [
            'id' => 10,
            'source' => 'merged',
            'source_game_id' => 'custom:1',
            'game_id' => $game->id,
            'game_title' => $game->title,
        ];

        $bindings = collect();
        DB::listen(function ($query) use ($bindings): void {
            $bindings->push(...$query->bindings);
        });

        $resolved = app(ReviewGameResolver::class)->resolveMany(collect([$review]));

        $this->assertTrue($resolved->get($review->id)->is($game));
        $this->assertFalse($bindings->contains('custom:1'));
    }
}
