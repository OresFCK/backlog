<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Game;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SitemapController extends Controller
{
    private const URLS_PER_SITEMAP = 45000;

    public function index(): Response
    {
        $gameCount = $this->gameSitemapQuery()->count();

        $gameSitemapCount = (int) ceil(
            $gameCount /
            self::URLS_PER_SITEMAP
        );
        $latestBlogPost = BlogPost::query()
            ->where('is_published', true)
            ->latest('updated_at')
            ->first(['updated_at']);

        $sitemaps = [
            [
                'loc' => route('sitemap.static'),
                'lastmod' => null,
            ],
            [
                'loc' => route('sitemap.blog'),
                'lastmod' => $latestBlogPost?->updated_at?->toAtomString()
                    ?? null,
            ],
        ];

        for (
            $page = 1;
            $page <= $gameSitemapCount;
            $page++
        ) {
            $sitemaps[] = [
                'loc' => route(
                    'sitemap.games',
                    ['page' => $page]
                ),
                'lastmod' => null,
            ];
        }

        return response()
            ->view(
                'sitemaps.index',
                [
                    'sitemaps' => $sitemaps,
                ]
            )
            ->header(
                'Content-Type',
                'application/xml; charset=UTF-8'
            )
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function staticPages(): StreamedResponse
    {
        $urls = [
            [
                'loc' => route('home'),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('about'),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            [
                'loc' => route('contact'),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('community-guidelines'),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('tier-lists.index'),
                'changefreq' => 'daily',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/privacy'),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => url('/terms'),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
        ];

        return response()->stream(
            function () use ($urls) {
                $this->startUrlSet();

                foreach ($urls as $url) {
                    $this->writeUrl(
                        loc: $url['loc'],
                        changefreq:
                            $url['changefreq'],
                        priority:
                            $url['priority']
                    );
                }

                $this->endUrlSet();
            },
            200,
            $this->xmlHeaders()
        );
    }

    public function games(
        int $page
    ): StreamedResponse {
        abort_if($page < 1, 404);

        $offset =
            ($page - 1) *
            self::URLS_PER_SITEMAP;

        $gameCount = $this->gameSitemapQuery()->count();

        abort_if(
            $offset >= $gameCount,
            404
        );

        return response()->stream(
            function () use ($offset) {
                $this->startUrlSet();

                $games = $this->gameSitemapQuery()
                    ->orderBy('id')
                    ->offset($offset)
                    ->limit(
                        self::URLS_PER_SITEMAP
                    )
                    ->cursor();

                foreach ($games as $game) {
                    $this->writeUrl(
                        loc: route(
                            'games.public.show',
                            [
                                'game' =>
                                    $game->slug,
                            ]
                        ),
                        lastmod:
                            $game->updated_at
                                ?->toAtomString(),
                        changefreq: 'weekly',
                        priority: '0.8'
                    );
                }

                $this->endUrlSet();
            },
            200,
            $this->xmlHeaders()
        );
    }

    public function blog(): StreamedResponse
    {
        $latestBlogPost = BlogPost::query()
            ->where('is_published', true)
            ->latest('updated_at')
            ->first(['updated_at']);

        return response()->stream(
            function () use ($latestBlogPost) {
                $this->startUrlSet();

                $this->writeUrl(
                    loc: route('blog.index'),
                    lastmod: $latestBlogPost?->updated_at?->toAtomString(),
                    changefreq: 'daily',
                    priority: '0.8'
                );

                $posts = BlogPost::query()
                    ->where('is_published', true)
                    ->orderBy('id')
                    ->cursor();

                foreach ($posts as $post) {
                    $this->writeUrl(
                        loc: route('blog.show', $post),
                        lastmod: $post->updated_at?->toAtomString(),
                        changefreq: 'weekly',
                        priority: '0.7'
                    );
                }

                $this->endUrlSet();
            },
            200,
            $this->xmlHeaders()
        );
    }

    private function startUrlSet(): void
    {
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        echo "\n";
    }

    private function gameSitemapQuery(): Builder
    {
        return Game::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereNotIn('slug', $this->reservedTopLevelSlugs());
    }

    /**
     * A game URL lives at /{slug}, so literal top-level application routes
     * always win during route matching and must never be emitted as game URLs.
     *
     * @return list<string>
     */
    private function reservedTopLevelSlugs(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => trim($route->uri(), '/'))
            ->filter(fn (string $uri) => $uri !== ''
                && ! str_contains($uri, '/')
                && ! str_contains($uri, '{'))
            ->unique()
            ->values()
            ->all();
    }

    private function endUrlSet(): void
    {
        echo '</urlset>';
        echo "\n";
    }

    private function writeUrl(
        string $loc,
        ?string $lastmod = null,
        ?string $changefreq = null,
        ?string $priority = null
    ): void {
        echo "    <url>\n";

        echo '        <loc>';
        echo $this->escapeXml($loc);
        echo "</loc>\n";

        if ($lastmod) {
            echo '        <lastmod>';
            echo $this->escapeXml($lastmod);
            echo "</lastmod>\n";
        }

        if ($changefreq) {
            echo '        <changefreq>';
            echo $this->escapeXml(
                $changefreq
            );
            echo "</changefreq>\n";
        }

        if ($priority) {
            echo '        <priority>';
            echo $this->escapeXml(
                $priority
            );
            echo "</priority>\n";
        }

        echo "    </url>\n";

        if (
            ob_get_level() > 0 &&
            ob_get_length() > 1024 * 1024
        ) {
            ob_flush();
            flush();
        }
    }

    private function escapeXml(
        string $value
    ): string {
        return htmlspecialchars(
            $value,
            ENT_XML1 | ENT_QUOTES,
            'UTF-8'
        );
    }

    private function xmlHeaders(): array
    {
        return [
            'Content-Type' =>
                'application/xml; charset=UTF-8',

            'Cache-Control' =>
                'public, max-age=3600',
        ];
    }
}
