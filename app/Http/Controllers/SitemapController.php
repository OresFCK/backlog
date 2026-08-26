<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Game;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SitemapController extends Controller
{
    private const URLS_PER_SITEMAP = 45000;

    public function index(): Response
    {
        $gameCount = Game::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->count();

        $gameSitemapCount = max(
            1,
            (int) ceil(
                $gameCount /
                self::URLS_PER_SITEMAP
            )
        );
        $latestBlogPost = BlogPost::query()
            ->where('is_published', true)
            ->latest('updated_at')
            ->first(['updated_at']);

        $sitemaps = [
            [
                'loc' => route('sitemap.static'),
                'lastmod' => now()->toAtomString(),
            ],
            [
                'loc' => route('sitemap.blog'),
                'lastmod' => $latestBlogPost?->updated_at?->toAtomString()
                    ?? now()->toAtomString(),
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
                'lastmod' => now()->toAtomString(),
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
            );
    }

    public function staticPages(): StreamedResponse
    {
        $urls = [
            [
                'loc' => url('/'),
                'changefreq' => 'daily',
                'priority' => '1.0',
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

        $gameCount = Game::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->count();

        abort_if(
            $offset >= $gameCount,
            404
        );

        return response()->stream(
            function () use ($offset) {
                $this->startUrlSet();

                $games = Game::query()
                    ->whereNotNull('slug')
                    ->where('slug', '!=', '')
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
        return response()->stream(
            function () {
                $this->startUrlSet();

                $this->writeUrl(
                    loc: route('blog.index'),
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
