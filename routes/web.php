<?php

use App\Helpers\PayloadHelper as Payload;
use App\Http\Controllers\Auth\SteamAuthController;
use App\Http\Requests\StoreCustomGameRequest;
use App\Http\Requests\StoreCustomLabelRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use App\Http\Requests\UpdateGameMetaRequest;
use App\Services\SteamService;
use Illuminate\Support\Facades\Route;
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

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', fn (SteamService $steam) =>
        Inertia::render(
            'dashboard',
            Payload::pageData($steam)
        )
    )->name('dashboard');

    Route::get('/backlog', fn (SteamService $steam) =>
        Inertia::render(
            'backlog/index',
            Payload::pageData($steam)
        )
    )->name('backlog.index');

    Route::get('/wishlist', fn (SteamService $steam) =>
        Inertia::render(
            'wishlist/index',
            Payload::wishlistPageData($steam)
        )
    )->name('wishlist.index');

    Route::get('/recommendations', fn (SteamService $steam) =>
        Inertia::render(
            'recommendations/index',
            Payload::pageData($steam)
        )
    )->name('recommendations.index');

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
});