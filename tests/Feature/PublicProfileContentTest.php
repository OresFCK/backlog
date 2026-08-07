<?php

namespace Tests\Feature;

use App\Helpers\PayloadHelper;
use App\Models\BlogPost;
use App\Models\PublicReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProfileContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_content_contains_all_public_reviews_and_posts(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();

        $review = PublicReview::query()->create([
            'user_id' => $author->id,
            'game_id' => '1',
            'game_title' => 'Public Game',
            'title' => 'Public Review',
            'body' => 'Review body',
            'rating' => 8,
            'is_public' => true,
        ]);
        PublicReview::query()->create([
            'user_id' => $author->id,
            'game_id' => '2',
            'game_title' => 'Private Game',
            'title' => 'Private Review',
            'body' => 'Hidden body',
            'rating' => 5,
            'is_public' => false,
        ]);
        $post = BlogPost::query()->create([
            'user_id' => $author->id,
            'title' => 'Published Post',
            'slug' => 'published-post',
            'body' => 'This is a published blog post body.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($viewer);
        $content = PayloadHelper::publicProfileContent($author);

        $this->assertSame([$review->id], $content['publicReviews']->pluck('id')->all());
        $this->assertSame([$post->id], $content['publicBlogPosts']->pluck('id')->all());
        $this->assertTrue($content['publicReviews']->first()['can_interact']);
    }

    public function test_logged_in_user_can_vote_for_public_review_without_connection(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();
        $review = PublicReview::query()->create([
            'user_id' => $author->id,
            'game_id' => '1',
            'game_title' => 'Game',
            'title' => 'Review',
            'body' => 'Review body',
            'rating' => 8,
            'is_public' => true,
        ]);

        $this->actingAs($viewer)
            ->post("/reviews/{$review->id}/vote", ['value' => 1])
            ->assertRedirect();

        $this->assertDatabaseHas('public_review_votes', [
            'public_review_id' => $review->id,
            'user_id' => $viewer->id,
            'value' => 1,
        ]);
    }
}
