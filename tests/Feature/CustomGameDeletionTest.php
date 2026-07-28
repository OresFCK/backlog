<?php

namespace Tests\Feature;

use App\Models\CustomGame;
use App\Models\User;
use App\Models\UserGameMeta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomGameDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_their_custom_game_and_metadata(): void
    {
        $user = User::factory()->create();
        $game = CustomGame::query()->create([
            'user_id' => $user->id,
            'title' => 'Mistyped game',
        ]);

        UserGameMeta::query()->create([
            'user_id' => $user->id,
            'game_id' => "custom-{$game->id}",
            'status' => 'Backlog',
        ]);

        $this->actingAs($user)
            ->delete(route('custom-games.destroy', $game))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('custom_games', ['id' => $game->id]);
        $this->assertDatabaseMissing('user_game_meta', [
            'user_id' => $user->id,
            'game_id' => "custom-{$game->id}",
        ]);
    }

    public function test_user_cannot_update_or_delete_another_users_custom_game(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $game = CustomGame::query()->create([
            'user_id' => $owner->id,
            'title' => 'Owners game',
        ]);

        $this->actingAs($otherUser)
            ->patch(route('custom-games.update', $game), [
                'title' => 'Stolen game',
            ])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->delete(route('custom-games.destroy', $game))
            ->assertForbidden();

        $this->assertDatabaseHas('custom_games', [
            'id' => $game->id,
            'title' => 'Owners game',
        ]);
    }
}
