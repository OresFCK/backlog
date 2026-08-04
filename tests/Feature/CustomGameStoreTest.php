<?php

namespace Tests\Feature;

use App\Models\CustomGame;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomGameStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_legacy_game_is_updated_instead_of_inserted_again(): void
    {
        $user = User::factory()->create();
        $game = CustomGame::query()->create([
            'user_id' => $user->id,
            'title' => 'Metal Gear Solid 4: Guns of the Patriots',
            'normalized_title' => null,
            'igdb_id' => null,
        ]);

        $this->actingAs($user)
            ->post(route('games.store'), [
                'title' => 'Metal Gear Solid 4: Guns of the Patriots',
                'publisher' => 'Konami',
                'igdb_id' => 380,
                'source' => 'igdb',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('custom_games', 1);
        $this->assertDatabaseHas('custom_games', [
            'id' => $game->id,
            'igdb_id' => 380,
            'publisher' => 'Konami',
        ]);
    }
}
