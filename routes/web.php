<?php

use App\Helpers\PayloadHelper as Payload;
use App\Http\Controllers\AdminChallengeController;
use App\Http\Controllers\AdminReviewReportController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\SteamAuthController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\PublicReviewController;
use App\Http\Controllers\PublicReviewReportController;
use App\Http\Controllers\PublicReviewVoteController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShopItemController;
use App\Http\Controllers\UserConnectionController;
use App\Http\Controllers\WardrobeController;
use App\Http\Requests\StoreCustomGameRequest;
use App\Http\Requests\StoreCustomLabelRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use App\Http\Requests\UpdateGameMetaRequest;
use App\Http\Requests\UpdateProfileBannerRequest;
use App\Http\Controllers\IgdbDumpController;
use App\Models\User;
use App\Services\SteamService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::redirect('/', '/home');

Route::inertia('/home', 'home')
    ->name('home');

Route::inertia('/login', 'auth/login')
    ->name('login');

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

    Route::get('/recommendations', [
        RecommendationController::class,
        'index',
    ])->name('recommendations.index');

    Route::get('/games/create', fn (SteamService $steam) =>
        Inertia::render('games/create', Payload::pageData($steam))
    )->name('games.create');

    Route::post('/games', fn (
        StoreCustomGameRequest $request
    ) => Payload::storeCustomGame($request))->name('games.store');

    Route::post('/statuses', fn (
        StoreCustomStatusRequest $request
    ) => Payload::storeStatus($request))->name('statuses.store');

    Route::post('/games/{game}/meta', fn (
        UpdateGameMetaRequest $request,
        string $game
    ) => Payload::storeMeta($request, $game))->name('games.meta');

    Route::get('/steam/search', fn (
        SteamService $steam
    ) => Payload::steamSearch($steam))->name('steam.search');

    Route::get('/settings/labels', fn () =>
        Inertia::render('settings/labels', [
            'user' => Payload::currentUser(),
            'labels' => Payload::customLabels(),
        ])
    )->name('settings.labels');

    Route::post('/settings/labels', fn (
        StoreCustomLabelRequest $request
    ) => Payload::storeCustomLabel($request))->name('settings.labels.store');

    Route::get('/games/{game}', fn (
        string $game,
        SteamService $steam
    ) => Inertia::render(
        'games/show',
        Payload::gamePageData($game, $steam)
    ))->name('games.show');

    Route::get('/profile', fn (
        SteamService $steam
    ) => Inertia::render(
        'profile/show',
        Payload::profilePageData($steam)
    ))->name('profile.show');

    Route::post('/profile/banner', function (
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
    })->name('profile.banner.update');

    Route::get('/dropped', fn (SteamService $steam) =>
        Inertia::render('dropped/index', Payload::droppedPageData($steam))
    )->name('dropped.index');

    Route::post('/reviews/public', [
        PublicReviewController::class,
        'store',
    ])->name('reviews.public.store');

    Route::get('/reviews', [
        PublicReviewController::class,
        'index',
    ])->name('reviews.index');

    Route::post('/reviews/{review}/feature', [
        PublicReviewController::class,
        'toggleFeatured',
    ])->name('reviews.feature');

    Route::post('/reviews/{review}/report', [
        PublicReviewReportController::class,
        'store',
    ])->name('reviews.report');

    Route::post('/reviews/{review}/vote', [
        PublicReviewVoteController::class,
        'store',
    ])->name('reviews.vote.store');

    Route::delete('/reviews/{review}/vote', [
        PublicReviewVoteController::class,
        'destroy',
    ])->name('reviews.vote.destroy');

    Route::get('/people', [
        UserConnectionController::class,
        'index',
    ])->name('people.index');

    Route::get('/people/search', [
        UserConnectionController::class,
        'search',
    ])->name('people.search');

    Route::get('/people/notifications', [
        UserConnectionController::class,
        'notifications',
    ])->name('people.notifications');

    Route::post('/people', [
        UserConnectionController::class,
        'store',
    ])->name('people.store');

    Route::patch('/people/{connection}/accept', [
        UserConnectionController::class,
        'accept',
    ])->name('people.accept');

    Route::delete('/people/{connection}', [
        UserConnectionController::class,
        'destroy',
    ])->name('people.destroy');

    Route::get('/shop', [ShopController::class, 'index'])
        ->name('shop.index');

    Route::post('/shop/{item}/buy', [ShopController::class, 'buy'])
        ->name('shop.buy');

    Route::post('/shop/{item}/equip', [ShopController::class, 'equip'])
        ->name('shop.equip');

    Route::get('/wardrobe', [WardrobeController::class, 'index'])
        ->name('wardrobe.index');

    Route::post('/wardrobe/{item}/equip', [WardrobeController::class, 'equip'])
        ->name('wardrobe.equip');

    Route::delete('/wardrobe/{item}/equip', [WardrobeController::class, 'unequip'])
        ->name('wardrobe.unequip');

    Route::post('/wardrobe/{item}/feature', [WardrobeController::class, 'toggleFeatured'])
        ->name('wardrobe.feature');

    Route::get('/challenges', [ChallengeController::class, 'index'])
        ->name('challenges.index');

    Route::post('/challenges/{challenge}/join', [ChallengeController::class, 'join'])
        ->name('challenges.join');

    Route::post('/challenges/{challenge}/submit', [ChallengeController::class, 'submit'])
        ->name('challenges.submit');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [ShopItemController::class, 'index'])
            ->name('index');

        Route::post('/shop-items', [ShopItemController::class, 'store'])
            ->name('shop-items.store');

        Route::put('/shop-items/{item}', [ShopItemController::class, 'update'])
            ->name('shop-items.update');

        Route::delete('/shop-items/{item}', [ShopItemController::class, 'destroy'])
            ->name('shop-items.destroy');

        Route::get('/challenges', [AdminChallengeController::class, 'index'])
            ->name('challenges.index');

        Route::post('/challenges', [AdminChallengeController::class, 'store'])
            ->name('challenges.store');

        Route::delete('/challenges/{challenge}', [AdminChallengeController::class, 'destroy'])
            ->name('challenges.destroy');

        Route::get('/users/search', [AdminUserController::class, 'search'])
            ->name('users.search');

        Route::get('/users/{user}/logs', [AdminUserController::class, 'logs'])
            ->name('users.logs');

        Route::post('/users/{user}/coins', [AdminUserController::class, 'addCoins'])
            ->name('users.coins');

        Route::post('/challenge-submissions/{submission}/approve', [AdminChallengeController::class, 'approve'])
            ->name('challenge-submissions.approve');

        Route::post('/challenge-submissions/{submission}/reject', [AdminChallengeController::class, 'reject'])
            ->name('challenge-submissions.reject');

        Route::patch('/review-reports/{report}/resolve', [
            AdminReviewReportController::class,
            'resolve',
        ])->name('review-reports.resolve');

        Route::delete('/review-reports/{report}/review', [
            AdminReviewReportController::class,
            'destroyReview',
        ])->name('review-reports.review.destroy');

        Route::post('/igdb/dumps/{endpoint}', [
            \App\Http\Controllers\IgdbDumpController::class,
            'show',
        ])->name('igdb.dumps.show');
        
        Route::post('/igdb/games/import', [IgdbDumpController::class, 'importGames']);
    });