<?php

use App\Helpers\CacheKeys;
use App\Helpers\PayloadHelper as Payload;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AdminChallengeController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\AdminReviewReportController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminUserSubmissionController;
use App\Http\Controllers\AnticipatedGameController;
use App\Http\Controllers\Auth\SteamAuthController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\BlogPostReportController;
use App\Http\Controllers\BlogPostVoteController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\CuratorController;
use App\Http\Controllers\CustomGameController;
use App\Http\Controllers\CustomListController;
use App\Http\Controllers\IgdbDumpController;
use App\Http\Controllers\IgdbGameSearchController;
use App\Http\Controllers\MiniCuratorController;
use App\Http\Controllers\PremiereController;
use App\Http\Controllers\PublicGameController;
use App\Http\Controllers\PublicGameSearchController;
use App\Http\Controllers\PublicReviewController;
use App\Http\Controllers\PublicReviewReportController;
use App\Http\Controllers\PublicReviewVoteController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShopItemController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TierListController;
use App\Http\Controllers\UserConnectionController;
use App\Http\Controllers\UserSubmissionController;
use App\Http\Controllers\WardrobeController;
use App\Http\Requests\StoreCustomGameRequest;
use App\Http\Requests\StoreCustomLabelRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use App\Http\Requests\UpdateCustomLabelRequest;
use App\Http\Requests\UpdateGameMetaRequest;
use App\Http\Requests\UpdateProfileBannerRequest;
use App\Models\CustomStatus;
use App\Models\TierList;
use App\Models\User;
use App\Services\RecommendationService;
use App\Services\SteamService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

Route::redirect('/', '/home');

Route::get('/home', function () {
    $tierLists = TierList::query()
        ->where('is_public', true)
        ->with('user')
        ->latest('published_at')
        ->limit(6)
        ->get()
        ->map(fn (TierList $tierList) => [
            'id' => $tierList->id,
            'title' => $tierList->title,
            'slug' => $tierList->slug,
            'description' => $tierList->description,
            'items_count' => count($tierList->data['items'] ?? []),
            'tiers' => collect($tierList->data['tiers'] ?? [])
                ->take(5)
                ->values(),
            'covers' => collect($tierList->data['items'] ?? [])
                ->pluck('cover_url')
                ->filter()
                ->unique()
                ->take(4)
                ->values(),
            'author' => $tierList->user?->visible_name,
            'url' => route('tier-lists.public.show', $tierList),
        ]);

    return Inertia::render('home', [
        'tierLists' => $tierLists,
    ]);
})->name('home');
Route::inertia('/login', 'auth/login')->name('login');

Route::inertia('/terms', 'terms')->name('terms');
Route::inertia('/privacy', 'privacy')->name('privacy');
Route::inertia('/about', 'about')->name('about');
Route::inertia('/contact', 'contact')->name('contact');
Route::inertia('/community-guidelines', 'community-guidelines')
    ->name('community-guidelines');

Route::get('/u/{user:steam_id}', fn (
    User $user,
    SteamService $steam
) => Inertia::render(
    'profile/public',
    [
        ...Payload::publicProfilePageData($user, $steam),
        ...Payload::publicProfileContent($user),
    ]
))->name('profile.public');

Route::controller(SteamAuthController::class)
    ->prefix('auth/steam')
    ->name('steam.')
    ->group(function () {
        Route::get('/', 'redirect')->name('redirect');
        Route::get('/callback', 'callback')->name('callback');
    });

Route::get('/invite/{steamId}', function (
    string $steamId,
    SteamService $steam
) {
    $profile = collect($steam->searchPlayer($steamId))->first();

    if (! $profile) {
        abort(404);
    }

    return Inertia::render('invite/show', [
        'profile' => $profile,
    ]);
})->name('invite.show');

Route::get('/shared/recommendations', [
    RecommendationController::class,
    'shared',
])->name('recommendations.shared');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (
        SteamService $steam,
        RecommendationService $recommendations
    ) {
        return Inertia::render('dashboard', [
            ...Payload::pageData($steam),
            'friendsRanking' => $recommendations->friendsRanking(),
            'globalRanking' => $recommendations->globalRanking(),
        ]);
    })->name('dashboard');

    Route::get('/curators', [
        CuratorController::class,
        'index',
    ])->name('curators.index');

    Route::get('/curators/game/{source}/{gameId}', [
        CuratorController::class,
        'showGame',
    ])->name('curators.game');

    Route::get('/premieres', [PremiereController::class, 'index'])
        ->name('premieres.index');

    Route::get('/premieres/month/{month}', [PremiereController::class, 'month'])
        ->name('premieres.month');

    Route::post(
        '/premieres/{gameIdentifier}/anticipate',
        [
            AnticipatedGameController::class,
            'toggle',
        ]
    )->name('premieres.anticipate');

    Route::get('/backlog', fn (SteamService $steam) => Inertia::render('backlog/index', Payload::backlogPageData($steam))
    )->name('backlog.index');

    Route::get('/playing', fn (SteamService $steam) => Inertia::render('playing/index', Payload::playingPageData($steam))
    )->name('playing.index');

    Route::get('/finished', fn (SteamService $steam) => Inertia::render('finished/index', Payload::finishedPageData($steam))
    )->name('finished.index');

    Route::get('/wishlist', fn (SteamService $steam) => Inertia::render('wishlist/index', Payload::wishlistPageData($steam))
    )->name('wishlist.index');

    Route::get('/dropped', fn (SteamService $steam) => Inertia::render('dropped/index', Payload::droppedPageData($steam))
    )->name('dropped.index');

    Route::get('/recommendations', [
        RecommendationController::class,
        'index',
    ])->name('recommendations.index');

    Route::get('/recommendations/artwork/{gameId}', [
        RecommendationController::class,
        'artwork',
    ])->name('recommendations.artwork');

    Route::get('/stats', [
        StatsController::class,
        'index',
    ])->name('stats.index');

    Route::get('/steam/search', fn (
        SteamService $steam
    ) => Payload::steamSearch($steam))->name('steam.search');

    Route::prefix('games')
        ->name('games.')
        ->group(function () {
            Route::get('/create', fn (SteamService $steam) => Inertia::render('games/create', Payload::pageData($steam))
            )->name('create');

            Route::post('/', fn (
                StoreCustomGameRequest $request
            ) => Payload::storeCustomGame($request))->name('store');

            Route::post('/{game}/meta', fn (
                UpdateGameMetaRequest $request,
                string $game
            ) => Payload::storeMeta($request, $game))->name('meta');

            Route::post('/bulk-status', fn () => Payload::bulkUpdateStatuses()
            )->name('bulk-status');

            Route::get('/{game}', function (
                string $game,
                SteamService $steam
            ) {
                try {
                    $data = Payload::gamePageData($game, $steam);
                } catch (NotFoundHttpException) {
                    return redirect()
                        ->route('dashboard')
                        ->with('no_product_card', true)
                        ->with('error', 'No product card');
                }

                if (blank($data['game'] ?? null)) {
                    return redirect()
                        ->route('dashboard')
                        ->with('error', 'No product card')
                        ->with('no_product_card', true);
                }

                return Inertia::render('games/show', $data);
            })->name('show');
        });

    Route::patch('/custom-games/{customGame}', [
        CustomGameController::class,
        'update',
    ])->name('custom-games.update');
    Route::delete('/custom-games/{customGame}', [
        CustomGameController::class,
        'destroy',
    ])->name('custom-games.destroy');

    Route::prefix('lists')
        ->name('lists.')
        ->group(function () {
            Route::get('/', [
                CustomListController::class,
                'index',
            ])->name('index');

            Route::post('/', [
                CustomListController::class,
                'store',
            ])->name('store');

            Route::get('/{list}', [
                CustomListController::class,
                'show',
            ])->name('show');

            Route::post('/{list}/items', [
                CustomListController::class,
                'storeItem',
            ])->name('items.store');

            Route::patch('/{list}/items/reorder', [
                CustomListController::class,
                'reorder',
            ])->name('items.reorder');

            Route::delete('/{list}/items/{item}', [
                CustomListController::class,
                'destroyItem',
            ])->name('items.destroy');

            Route::patch('/{list}', [CustomListController::class, 'update'])
                ->name('lists.update');

            Route::delete('/{list}', [CustomListController::class, 'destroy'])
                ->name('lists.destroy');
        });

    Route::prefix('tier-lists')
        ->name('tier-lists.')
        ->group(function () {
            Route::post('/', [TierListController::class, 'store'])
                ->name('store');
            Route::get('/{tierList:slug}/edit', [TierListController::class, 'edit'])
                ->name('edit');
            Route::patch('/{tierList:slug}', [TierListController::class, 'update'])
                ->name('update');
            Route::delete('/{tierList:slug}', [TierListController::class, 'destroy'])
                ->name('destroy');
        });

    Route::prefix('blog')
        ->name('blog.')
        ->group(function () {
            Route::get('/mine', [BlogPostController::class, 'mine'])
                ->name('mine');
            Route::get('/create', [BlogPostController::class, 'create'])
                ->name('create');
            Route::post('/', [BlogPostController::class, 'store'])
                ->name('store');
            Route::get('/{post:slug}/edit', [BlogPostController::class, 'edit'])
                ->name('edit');
            Route::patch('/{post:slug}', [BlogPostController::class, 'update'])
                ->name('update');
            Route::delete('/{post:slug}', [BlogPostController::class, 'destroy'])
                ->name('destroy');
            Route::post('/{post:slug}/vote', [BlogPostVoteController::class, 'store'])
                ->name('vote.store');
            Route::delete('/{post:slug}/vote', [BlogPostVoteController::class, 'destroy'])
                ->name('vote.destroy');
            Route::post('/{post:slug}/report', [BlogPostReportController::class, 'store'])
                ->name('report.store');
        });

    Route::post('/statuses', fn (
        StoreCustomStatusRequest $request
    ) => Payload::storeStatus($request))->name('statuses.store');

    Route::prefix('settings')
        ->name('settings.')
        ->group(function () {
            Route::get('/labels', fn () => Inertia::render('settings/labels', [
                'user' => Payload::currentUser(),
                'labels' => Payload::customLabels(),
            ])
            )->name('labels');

            Route::post('/labels', fn (
                StoreCustomLabelRequest $request
            ) => Payload::storeCustomLabel($request))->name('labels.store');

            Route::put('/labels/{customLabel}', fn (
                UpdateCustomLabelRequest $request,
                CustomStatus $customLabel
            ) => Payload::updateCustomLabel(
                $request,
                $customLabel
            ))->name('labels.update');

            Route::delete('/labels/{customLabel}', fn (
                CustomStatus $customLabel
            ) => Payload::deleteCustomLabel(
                $customLabel
            ))->name('labels.destroy');

            Route::get('/report-bug', [
                UserSubmissionController::class,
                'bug',
            ])->name('report-bug');

            Route::get('/suggestion', [
                UserSubmissionController::class,
                'suggestion',
            ])->name('suggestion');

            Route::post('/submissions', [
                UserSubmissionController::class,
                'store',
            ])->name('submissions.store');

            Route::get('/account', [
                AccountSettingsController::class,
                'edit',
            ])->name('account');

            Route::patch('/account', [
                AccountSettingsController::class,
                'update',
            ])->name('account.update');
        });

    Route::middleware('auth')->group(function () {
        Route::get('/mini-curators', [
            MiniCuratorController::class,
            'index',
        ])->name('mini-curators.index');

        Route::post('/mini-curators/{user}/follow', [
            MiniCuratorController::class,
            'follow',
        ])->name('mini-curators.follow');

        Route::delete('/mini-curators/{user}/follow', [
            MiniCuratorController::class,
            'unfollow',
        ])->name('mini-curators.unfollow');
    });

    Route::prefix('profile')
        ->name('profile.')
        ->group(function () {
            Route::get('/', fn (
                SteamService $steam
            ) => Inertia::render(
                'profile/show',
                Payload::profilePageData($steam)
            ))->name('show');

            Route::post('/banner', function (
                UpdateProfileBannerRequest $request
            ) {
                $user = $request->user();

                if ($user->banner_url) {
                    Storage::disk('public')->delete(
                        str_replace('/storage/', '', $user->banner_url)
                    );
                }

                $path = $request
                    ->file('banner')
                    ->store('banners', 'public');

                $user->update([
                    'banner_url' => "/storage/{$path}",
                ]);

                return back();
            })->name('banner.update');

            Route::patch('/curator', function () {
                $user = auth()->user();
                $user->update([
                    'is_curator' => ! $user->is_curator,
                ]);
                Cache::forget(CacheKeys::profilePage($user->id));

                return back();
            })->name('curator.toggle');
        });

    Route::prefix('reviews')
        ->name('reviews.')
        ->group(function () {
            Route::get('/', [
                PublicReviewController::class,
                'index',
            ])->name('index');

            Route::get('/mine', [
                PublicReviewController::class,
                'mine',
            ])->name('mine');

            Route::post('/public', [
                PublicReviewController::class,
                'store',
            ])->name('public.store');

            Route::post('/{review}/feature', [
                PublicReviewController::class,
                'toggleFeatured',
            ])->name('feature');

            Route::post('/{review}/report', [
                PublicReviewReportController::class,
                'store',
            ])->name('report');

            Route::post('/{review}/vote', [
                PublicReviewVoteController::class,
                'store',
            ])->name('vote.store');

            Route::delete('/{review}/vote', [
                PublicReviewVoteController::class,
                'destroy',
            ])->name('vote.destroy');
        });

    Route::prefix('people')
        ->name('people.')
        ->group(function () {
            Route::get('/', [
                UserConnectionController::class,
                'index',
            ])->name('index');

            Route::get('/search', [
                UserConnectionController::class,
                'search',
            ])->name('search');

            Route::get('/notifications', [
                UserConnectionController::class,
                'notifications',
            ])->name('notifications');

            Route::post('/notifications/read', [
                UserConnectionController::class,
                'markNotificationsAsRead',
            ])->name('notifications.read');

            Route::post('/', [
                UserConnectionController::class,
                'store',
            ])->name('store');

            Route::patch('/{connection}/accept', [
                UserConnectionController::class,
                'accept',
            ])->name('accept');

            Route::delete('/{connection}', [
                UserConnectionController::class,
                'destroy',
            ])->name('destroy');
        });

    Route::prefix('shop')
        ->name('shop.')
        ->group(function () {
            Route::get('/', [
                ShopController::class,
                'index',
            ])->name('index');

            Route::post('/{item}/buy', [
                ShopController::class,
                'buy',
            ])->name('buy');

            Route::post('/{item}/equip', [
                ShopController::class,
                'equip',
            ])->name('equip');
        });

    Route::prefix('wardrobe')
        ->name('wardrobe.')
        ->group(function () {
            Route::get('/', [
                WardrobeController::class,
                'index',
            ])->name('index');

            Route::post('/{item}/equip', [
                WardrobeController::class,
                'equip',
            ])->name('equip');

            Route::delete('/{item}/equip', [
                WardrobeController::class,
                'unequip',
            ])->name('unequip');

            Route::post('/{item}/feature', [
                WardrobeController::class,
                'toggleFeatured',
            ])->name('feature');
        });

    Route::prefix('challenges')
        ->name('challenges.')
        ->group(function () {
            Route::get('/', [
                ChallengeController::class,
                'index',
            ])->name('index');

            Route::post('/{challenge}/join', [
                ChallengeController::class,
                'join',
            ])->name('join');

            Route::post('/{challenge}/submit', [
                ChallengeController::class,
                'submit',
            ])->name('submit');
        });
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [
            ShopItemController::class,
            'index',
        ])->name('index');

        Route::get('/grantables', [
            AdminUserController::class,
            'grantables',
        ])->name('grantables');

        Route::prefix('shop-items')
            ->name('shop-items.')
            ->group(function () {
                Route::post('/', [
                    ShopItemController::class,
                    'store',
                ])->name('store');

                Route::put('/{item}', [
                    ShopItemController::class,
                    'update',
                ])->name('update');
            });

        Route::prefix('users')
            ->name('users.')
            ->group(function () {
                Route::get('/search', [
                    AdminUserController::class,
                    'search',
                ])->name('search');

                Route::get('/{user}/logs', [
                    AdminUserController::class,
                    'logs',
                ])->name('logs');

                Route::get('/{user}/available-challenges', [
                    AdminUserController::class,
                    'availableChallenges',
                ])->name('available-challenges');

                Route::post('/{user}/coins', [
                    AdminUserController::class,
                    'addCoins',
                ])->name('coins');

                Route::post('/{user}/xp', [
                    AdminUserController::class,
                    'addXp',
                ])->name('xp');

                Route::post('/{user}/level', [
                    AdminUserController::class,
                    'setLevel',
                ])->name('level');

                Route::post('/{user}/items', [
                    AdminUserController::class,
                    'grantItem',
                ])->name('items');

                Route::post('/{user}/challenges', [
                    AdminUserController::class,
                    'completeChallenge',
                ])->name('challenges');
            });

        Route::prefix('user-submissions')
            ->name('user-submissions.')
            ->group(function () {
                Route::patch('/{submission}/resolve', [
                    AdminUserSubmissionController::class,
                    'resolve',
                ])->name('resolve');

                Route::delete('/{submission}', [
                    AdminUserSubmissionController::class,
                    'destroy',
                ])->name('destroy');
            });

        Route::prefix('challenges')
            ->name('challenges.')
            ->group(function () {
                Route::get('/', [
                    AdminChallengeController::class,
                    'index',
                ])->name('index');

                Route::post('/', [
                    AdminChallengeController::class,
                    'store',
                ])->name('store');

                Route::delete('/{challenge}', [
                    AdminChallengeController::class,
                    'destroy',
                ])->name('destroy');
            });

        Route::prefix('challenge-submissions')
            ->name('challenge-submissions.')
            ->group(function () {
                Route::post('/{submission}/approve', [
                    AdminChallengeController::class,
                    'approve',
                ])->name('approve');

                Route::post('/{submission}/reject', [
                    AdminChallengeController::class,
                    'reject',
                ])->name('reject');
            });

        Route::prefix('reviews')
            ->name('reviews.')
            ->group(function () {
                Route::get('/{review}', [
                    AdminReviewController::class,
                    'show',
                ])->name('show');
            });

        Route::prefix('review-reports')
            ->name('review-reports.')
            ->group(function () {
                Route::patch('/{report}/resolve', [
                    AdminReviewReportController::class,
                    'resolve',
                ])->name('resolve');

                Route::delete('/{report}/review', [
                    AdminReviewReportController::class,
                    'destroyReview',
                ])->name('review.destroy');
            });

        Route::prefix('igdb')
            ->name('igdb.')
            ->group(function () {
                Route::post('/dumps/{endpoint}', [
                    IgdbDumpController::class,
                    'show',
                ])->name('dumps.show');

                Route::post('/games/import', [
                    IgdbDumpController::class,
                    'importGames',
                ])->name('games.import');

                Route::post('/sync', [
                    IgdbDumpController::class,
                    'syncCatalog',
                ])->name('sync');
            });
    });

Route::get('/igdb/search', [
    IgdbGameSearchController::class,
    'index',
])->name('igdb.search');

Route::get('/sitemap.xml', [
    SitemapController::class,
    'index',
])->name('sitemap.index');

Route::get('/sitemaps/static.xml', [
    SitemapController::class,
    'staticPages',
])->name('sitemap.static');

Route::get('/sitemaps/games-{page}.xml', [
    SitemapController::class,
    'games',
])
    ->whereNumber('page')
    ->name('sitemap.games');

Route::get('/shared/reviews/{review}', [
    PublicReviewController::class,
    'show',
])->name('reviews.public.show');

Route::get('/tier-list-maker', [TierListController::class, 'maker'])
    ->name('tier-lists.maker');

Route::get('/tier-lists', [TierListController::class, 'index'])
    ->name('tier-lists.index');

Route::get('/tier-lists/{tierList:slug}', [TierListController::class, 'show'])
    ->name('tier-lists.public.show');

Route::get('/blog', [BlogPostController::class, 'index'])
    ->name('blog.index');

Route::get('/blog/{post:slug}', [BlogPostController::class, 'show'])
    ->name('blog.show');

Route::get('/{game:slug}', [
    PublicGameController::class,
    'show',
])
    ->where('game', '[a-z0-9][a-z0-9-]*')
    ->name('games.public.show');

Route::get('/public-games/search', [PublicGameSearchController::class, 'index'])
    ->name('public-games.search');
