<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Storage;

class BlogPostEditingTest extends TestCase
{
    use RefreshDatabase;

    private function postWithImages(): BlogPost
    {
        Storage::fake('public');
        foreach (['blog/a.jpg', 'blog/b.jpg', 'blog/c.jpg'] as $path) {
            Storage::disk('public')->put($path, 'test image');
        }

        return BlogPost::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'A test blog post',
            'slug' => 'a-test-blog-post',
            'body' => 'A sufficiently long test body. [[image:1]]',
            'image_paths' => ['blog/a.jpg', 'blog/b.jpg', 'blog/c.jpg'],
            'is_published' => true,
        ]);
    }

    private function updateData(BlogPost $post): array
    {
        return [
            'title' => $post->title,
            'body' => $post->body,
            'is_published' => true,
        ];
    }

    public function test_owner_can_remove_and_reorder_saved_images(): void
    {
        $post = $this->postWithImages();
        $this->actingAs($post->user)->patch(route('blog.update', $post), [
            ...$this->updateData($post),
            'retained_images' => [2, 0],
            'body' => 'A sufficiently long test body. [[image:2]]',
        ])->assertSessionHasNoErrors()->assertRedirect(route('blog.mine'));

        $this->assertSame(['blog/c.jpg', 'blog/a.jpg'], $post->fresh()->image_paths);
        Storage::disk('public')->assertMissing('blog/b.jpg');
        Storage::disk('public')->assertExists(['blog/a.jpg', 'blog/c.jpg']);
    }

    public function test_owner_can_remove_all_images(): void
    {
        $post = $this->postWithImages();
        $this->actingAs($post->user)->patch(route('blog.update', $post), [
            ...$this->updateData($post),
            'remove_images' => true,
            'body' => 'A sufficiently long test body without images.',
        ])->assertSessionHasNoErrors();

        $this->assertSame([], $post->fresh()->image_paths);
        Storage::disk('public')->assertMissing(['blog/a.jpg', 'blog/b.jpg', 'blog/c.jpg']);
    }

    public function test_invalid_image_index_does_not_delete_files(): void
    {
        $post = $this->postWithImages();
        $this->actingAs($post->user)->patch(route('blog.update', $post), [
            ...$this->updateData($post),
            'retained_images' => [99],
        ])->assertSessionHasErrors('retained_images.0');

        $this->assertSame($post->image_paths, $post->fresh()->image_paths);
        Storage::disk('public')->assertExists($post->image_paths);
    }

    public function test_vote_is_unique_can_change_direction_and_can_be_removed(): void
    {
        $post = $this->postWithImages();
        $this->actingAs(User::factory()->create());
        foreach ([1, 1, -1] as $value) {
            $this->post(route('blog.vote.store', $post), ['value' => $value])
                ->assertSessionHasNoErrors();
            $this->assertSame(1, $post->votes()->count());
            $this->assertSame($value, (int) $post->votes()->sum('value'));
        }

        $this->delete(route('blog.vote.destroy', $post))->assertRedirect();
        $this->assertSame(0, $post->votes()->count());
    }
}
