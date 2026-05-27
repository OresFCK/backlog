<?php

use App\Helpers\PayloadHelper as Payload;
use App\Http\Controllers\Auth\SteamAuthController;
use App\Http\Controllers\PublicReviewController;
use App\Http\Controllers\PublicReviewVoteController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\UserConnectionController;
use App\Http\Requests\StoreCustomGameRequest;
use App\Http\Requests\StoreCustomLabelRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use App\Http\Requests\UpdateGameMetaRequest;
use App\Http\Requests\UpdateProfileBannerRequest;
use App\Services\RecommendationService;
use App\Services\SteamService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::redirect('/', '/home');

Route::inertia('/home', 'home')
    ->name('home');

Route::inertia('/login', 'auth/login')
    ->name('login');

Route::controller(SteamAuthController::class)
    ->prefix('auth/steam')
    ->name('steam.')
    ->group(function () {

        Route::get('/', 'redirect')
            ->name('redirect');

        Route::get('/callback', 'callback')
            ->name('callback');
    });

Route::get('/invite/{steamId}', function (
    string $steamId,
    SteamService $steam
) {

    $profile = collect(
        $steam->searchPlayer($steamId)
    )->first();

    if (! $profile) {
        abort(404);
    }

    return Inertia::render(
        'invite/show',
        [
            'profile' => $profile,
        ]
    );
})->name('invite.show');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function (
        SteamService $steam,
        RecommendationService $recommendations
    ) {

        return Inertia::render(
            'dashboard',
            [
                ...Payload::pageData($steam),

                'friendsRanking' =>
                    $recommendations->friendsRanking(),

                'globalRanking' =>
                    $recommendations->globalRanking(),
            ]
        );
    })->name('dashboard');

    Route::get('/backlog', fn (SteamService $steam) =>
        Inertia::render(
            'backlog/index',
            Payload::backlogPageData($steam)
        )
    )->name('backlog.index');

    Route::get('/playing', fn (SteamService $steam) =>
        Inertia::render(
            'playing/index',
            Payload::playingPageData($steam)
        )
    )->name('playing.index');

    Route::get('/finished', fn (SteamService $steam) =>
        Inertia::render(
            'finished/index',
            Payload::finishedPageData($steam)
        )
    )->name('finished.index');

    Route::get('/wishlist', fn (SteamService $steam) =>
        Inertia::render(
            'wishlist/index',
            Payload::wishlistPageData($steam)
        )
    )->name('wishlist.index');

    Route::get('/recommendations', [
        RecommendationController::class,
        'index',
    ])->name('recommendations.index');

    Route::get('/games/create', fn (SteamService $steam) =>
        Inertia::render(
            'games/create',
            Payload::pageData($steam)
        )
    )->name('games.create');

    Route::post('/games', fn (
        StoreCustomGameRequest $request
    ) => Payload::storeCustomGame($request))
        ->name('games.store');

    Route::post('/statuses', fn (
        StoreCustomStatusRequest $request
    ) => Payload::storeStatus($request))
        ->name('statuses.store');

    Route::post('/games/{game}/meta', fn (
        UpdateGameMetaRequest $request,
        string $game
    ) => Payload::storeMeta($request, $game))
        ->name('games.meta');

    Route::get('/steam/search', fn (
        SteamService $steam
    ) => Payload::steamSearch($steam))
        ->name('steam.search');

    Route::get('/settings/labels', fn () =>
        Inertia::render(
            'settings/labels',
            [
                'user' => Payload::currentUser(),
                'labels' => Payload::customLabels(),
            ]
        )
    )->name('settings.labels');

    Route::post('/settings/labels', fn (
        StoreCustomLabelRequest $request
    ) => Payload::storeCustomLabel($request))
        ->name('settings.labels.store');

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
                str_replace(
                    '/storage/',
                    '',
                    $user->banner_url
                )
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
        Inertia::render(
            'dropped/index',
            Payload::droppedPageData($steam)
        )
    )->name('dropped.index');

    Route::post('/reviews/public', [
        PublicReviewController::class,
        'store',
    ])->name('reviews.public.store');

    Route::get('/reviews', [
        PublicReviewController::class,
        'index',
    ])->name('reviews.index');

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
});