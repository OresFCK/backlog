<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_static_sitemap_contains_final_public_urls_not_root_redirect(): void
    {
        $response = $this->get(route('sitemap.static'))->assertOk();
        $xml = $response->streamedContent();

        $this->assertStringContainsString('<loc>'.route('home').'</loc>', $xml);
        $this->assertStringContainsString('<loc>'.route('about').'</loc>', $xml);
        $this->assertStringContainsString('<loc>'.route('contact').'</loc>', $xml);
        $this->assertStringNotContainsString('<loc>'.url('/').'</loc>', $xml);
    }

    public function test_game_sitemap_excludes_slugs_owned_by_literal_routes(): void
    {
        Game::create(['title' => 'Privacy', 'slug' => 'privacy']);
        Game::create(['title' => 'A Real Game', 'slug' => 'a-real-game']);

        $response = $this->get(route('sitemap.games', 1))->assertOk();
        $xml = $response->streamedContent();

        $this->assertStringNotContainsString('<loc>'.url('/privacy').'</loc>', $xml);
        $this->assertStringContainsString('<loc>'.url('/a-real-game').'</loc>', $xml);
    }

    public function test_blog_index_url_uses_latest_post_update_as_last_modified_date(): void
    {
        $post = BlogPost::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Indexed article',
            'slug' => 'indexed-article',
            'body' => 'A sufficiently long article body for sitemap testing.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $xml = $this->get(route('sitemap.blog'))
            ->assertOk()
            ->streamedContent();

        $this->assertMatchesRegularExpression(
            '#<loc>'.preg_quote(route('blog.index'), '#').'</loc>\s*<lastmod>'.preg_quote($post->updated_at->toAtomString(), '#').'</lastmod>#',
            $xml,
        );
    }

    public function test_sitemap_index_is_publicly_cacheable_without_fake_last_modified_dates(): void
    {
        Game::create(['title' => 'A Real Game', 'slug' => 'a-real-game']);

        $response = $this->get(route('sitemap.index'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'public, max-age=3600')
            ->assertHeaderMissing('Set-Cookie')
            ->assertHeaderMissing('Vary');

        $content = $response->getContent();
        $this->assertDoesNotMatchRegularExpression(
            '#<loc>'.preg_quote(route('sitemap.games', 1), '#').'</loc>\s*<lastmod>#',
            $content,
        );
    }
}
