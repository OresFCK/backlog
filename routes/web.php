<?php

use App\Helpers\PayloadHelper as Payload;
use App\Http\Controllers\AdminChallengeController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\AdminReviewReportController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\SteamAuthController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\CustomGameController;
use App\Http\Controllers\IgdbDumpController;
use App\Http\Controllers\IgdbGameSearchController;
use App\Http\Controllers\PublicReviewController;
use App\Http\Controllers\PublicReviewReportController;
use App\Http\Controllers\PublicReviewVoteController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShopItemController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserConnectionController;
use App\Http\Controllers\WardrobeController;
use App\Http\Requests\StoreCustomGameRequest;
use App\Http\Requests\StoreCustomLabelRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use App\Http\Requests\UpdateGameMetaRequest;
use App\Http\Requests\UpdateProfileBannerRequest;
use App\Http\Controllers\UserSubmissionController;
use App\Http\Controllers\AdminUserSubmissionController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\CuratorController;
use App\Http\Controllers\PublicGameController;
use App\Http\Controllers\CustomListController;
use App\Http\Controllers\PublicGameSearchController;
use App\Models\Game;
use Illuminate\Support\Facades\Response;
use App\Models\UserSubmission;
use App\Models\User;
use App\Services\SteamService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::redirect('/', '/home');

Route::inertia('/home', 'home')->name('home');
Route::inertia('/home', 'auth/home')->name('login');

Route::inertia('/terms', 'terms')->name('terms');
Route::inertia('/privacy', 'privacy')->name('privacy');

Route::get('/u/{user:steam_id}', fn (
    User $user,
    SteamService $steam
) => Inertia::render(
    'profile/public',
    Payload::publicProfilePageData($user, $steam)
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

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (
        SteamService $steam,
        \App\Services\RecommendationService $recommendations
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

    Route::get('/backlog', fn (SteamService $steam) =>
        Inertia::render('backlog/index', Payload::backlogPageData($steam))
    )->name('backlog.index');

    Route::get('/playing', fn (SteamService $steam) =>
        Inertia::render('playing/index', Payload::playingPageData($steam))
    )->name('playing.index');

    Route::get('/finished', fn (SteamService $steam) =>
        Inertia::render('finished/index', Payload::finishedPageData($steam))
    )->name('finished.index');

    Route::get('/wishlist', fn (SteamService $steam) =>
        Inertia::render('wishlist/index', Payload::wishlistPageData($steam))
    )->name('wishlist.index');

    Route::get('/dropped', fn (SteamService $steam) =>
        Inertia::render('dropped/index', Payload::droppedPageData($steam))
    )->name('dropped.index');

    Route::get('/recommendations', [
        RecommendationController::class,
        'index',
    ])->name('recommendations.index');

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
            Route::get('/create', fn (SteamService $steam) =>
                Inertia::render('games/create', Payload::pageData($steam))
            )->name('create');

            Route::post('/', fn (
                StoreCustomGameRequest $request
            ) => Payload::storeCustomGame($request))->name('store');

            Route::post('/{game}/meta', fn (
                UpdateGameMetaRequest $request,
                string $game
            ) => Payload::storeMeta($request, $game))->name('meta');

            Route::post('/bulk-status', fn () =>
                Payload::bulkUpdateStatuses()
            )->name('bulk-status');

            Route::get('/{game}', function (
                string $game,
                SteamService $steam
            ) {
                try {
                    $data = Payload::gamePageData($game, $steam);
                } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
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

    Route::post('/statuses', fn (
        StoreCustomStatusRequest $request
    ) => Payload::storeStatus($request))->name('statuses.store');

    Route::prefix('settings')
        ->name('settings.')
        ->group(function () {
            Route::get('/labels', fn () =>
                Inertia::render('settings/labels', [
                    'user' => Payload::currentUser(),
                    'labels' => Payload::customLabels(),
                ])
            )->name('labels');

            Route::post('/labels', fn (
                StoreCustomLabelRequest $request
            ) => Payload::storeCustomLabel($request))->name('labels.store');

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
        });

    Route::prefix('reviews')
        ->name('reviews.')
        ->group(function () {
            Route::get('/', [
                PublicReviewController::class,
                'index',
            ])->name('index');

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

Route::get('/{game:slug}', [
    PublicGameController::class,
    'show',
])
    ->where('game', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('games.public.show');

Route::get('/sitemap.xml', function () {
    $urls = [
        [
            'loc' => 'https://curator.gg/',
            'changefreq' => 'daily',
            'priority' => '1.0',
        ],
        [
            'loc' => 'https://curator.gg/privacy',
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ],
        [
            'loc' => 'https://curator.gg/terms',
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ],
    ];

    $games = Game::query()
        ->whereNotNull('slug')
        ->where('slug', '!=', '')
        ->latest('updated_at')
        ->limit(50000)
        ->get(['slug', 'updated_at']);

    foreach ($games as $game) {
        $urls[] = [
            'loc' => 'https://curator.gg/' . $game->slug,
            'lastmod' => $game->updated_at?->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ];
    }

    return Response::view('sitemap', [
        'urls' => $urls,
    ])->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/public-games/search', [PublicGameSearchController::class, 'index'])
    ->name('public-games.search');