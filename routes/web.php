<?php

use App\Http\Controllers\Auth\SteamAuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Services\SteamService;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return Inertia::render('dashboard');
})->name('dashboard');

Route::get('/backlog', function () {
    return Inertia::render('backlog/index');
})->name('backlog.index');

Route::get('/wishlist', function () {
    return Inertia::render('wishlist/index');
})->name('wishlist.index');

Route::get('/recommendations', function () {
    return Inertia::render('recommendations/index');
})->name('recommendations.index');

Route::get('/login', function () {
    return Inertia::render('auth/login');
})->name('login');

/* Steam Auth */

Route::get('/auth/steam', [SteamAuthController::class, 'redirect'])
    ->name('steam.redirect');

Route::get('/auth/steam/callback', [SteamAuthController::class, 'callback'])
    ->name('steam.callback');

Route::get('/wip', function (SteamService $steam) {
    $user = Auth::user();

    if (! $user) {
        return Inertia::render('wip', [
            'user' => null,
            'games' => [],
            'steam_error' => 'Not logged in.',
        ]);
    }

    $games = $steam->getOwnedGames($user->steam_id);

    return Inertia::render('wip', [
        'user' => [
            'name' => $user->name,
            'steam_id' => $user->steam_id,
            'avatar' => $user->steam_avatar_url,
        ],
        'games' => $games,
        'steam_error' => null,
    ]);
})->name('wip');