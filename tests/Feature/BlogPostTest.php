<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\BlogPostReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BlogPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_blog_only_lists_published_posts(): void
    {
        $user = User::factory()->create();

        BlogPost::query()->create([
            'user_id' => $user->id,
            'title' => 'Published article',
            'slug' => 'published-article',
            'body' => str_repeat('Published content. ', 3),
            'is_published' => true,
            'published_at' => now(),
        ]);

        BlogPost::query()->create([
            'user_id' => $user->id,
            'title' => 'Private draft',
            'slug' => 'private-draft',
            'body' => str_repeat('Draft content. ', 3),
            'is_published' => false,
        ]);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('blog/index')
                ->has('posts.data', 1)
                ->where('posts.data.0.title', 'Published article'));

        $this->get(route('blog.show', 'private-draft'))->assertNotFound();
    }

    public function test_user_can_create_and_update_a_blog_post(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('blog.store'), [
                'title' => 'My gaming story',
                'excerpt' => 'A short summary.',
                'body' => str_repeat('This is my story. ', 3),
                'is_published' => false,
            ])
            ->assertRedirect();

        $post = BlogPost::query()->firstOrFail();
        $this->assertFalse($post->is_published);

        $this->actingAs($user)
            ->patch(route('blog.update', $post), [
                'title' => 'My gaming story',
                'excerpt' => 'A short summary.',
                'body' => str_repeat('This is the updated story. ', 3),
                'is_published' => true,
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue($post->fresh()->is_published);
        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_user_can_vote_and_report_another_users_post(): void
    {
        $author = User::factory()->create();
        $reader = User::factory()->create();
        $post = BlogPost::query()->create([
            'user_id' => $author->id,
            'title' => 'An article',
            'slug' => 'an-article',
            'body' => str_repeat('Article content. ', 3),
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($reader)
            ->post(route('blog.vote.store', $post), ['value' => 1])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('blog_post_votes', [
            'blog_post_id' => $post->id,
            'user_id' => $reader->id,
            'value' => 1,
        ]);

        $this->actingAs($reader)
            ->post(route('blog.report.store', $post), [
                'reason' => 'This contains inappropriate content.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, BlogPostReport::query()->count());
    }

    public function test_user_can_upload_images_and_embed_a_youtube_video(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('blog.store'), [
                'title' => 'Post with media',
                'body' => str_repeat('This post contains media. ', 2),
                'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
                'images' => [
                    UploadedFile::fake()->image('cover.jpg', 1200, 630),
                ],
                'is_published' => true,
            ])
            ->assertSessionHasNoErrors();

        $post = BlogPost::query()->firstOrFail();
        $this->assertCount(1, $post->image_paths);
        Storage::disk('public')->assertExists($post->image_paths[0]);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where(
                    'post.youtube_embed_url',
                    'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'
                )
                ->has('post.images', 1));
    }
}
