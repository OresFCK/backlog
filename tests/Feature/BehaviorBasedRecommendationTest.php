<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\PublicReview;
use App\Models\User;
use App\Models\UserSteamGame;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BehaviorBasedRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_play_history_outweighs_equal_community_signals(): void
    {
        $user = User::factory()->create();
        $reviewer = User::factory()->create();

        Game::query()->create([
            'steam_app_id' => 100,
            'title' => 'Played Action Game',
            'slug' => 'played-action-game',
            'genres' => [5],
        ]);

        $matching = Game::query()->create([
            'steam_app_id' => 200,
            'title' => 'Matching Action Game',
            'slug' => 'matching-action-game',
            'genres' => [5],
        ]);

        $unrelated = Game::query()->create([
            'steam_app_id' => 300,
            'title' => 'Unrelated Puzzle Game',
            'slug' => 'unrelated-puzzle-game',
            'genres' => [9],
        ]);

        UserSteamGame::query()->create([
            'user_id' => $user->id,
            'steam_app_id' => '100',
            'name' => 'Played Action Game',
            'playtime_forever' => 2400,
            'last_played_at' => now()->subDays(2),
        ]);

        foreach ([$matching, $unrelated] as $game) {
            PublicReview::query()->create([
                'user_id' => $reviewer->id,
                'game_id' => (string) $game->id,
                'source' => 'steam',
                'source_game_id' => (string) $game->steam_app_id,
                'game_title' => $game->title,
                'body' => 'Recommended game.',
                'rating' => 8,
                'recommended' => true,
            ]);
        }

        $this->actingAs($user);

        $recommendations = app(RecommendationService::class)
            ->steamRecommendations();

        $this->assertSame(
            $matching->id,
            (int) $recommendations[0]['game']['id']
        );
        $this->assertGreaterThan(
            $recommendations[1]['behavior_score'],
            $recommendations[0]['behavior_score']
        );
    }
}
