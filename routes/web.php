<?php

use App\Http\Controllers\Auth\SteamAuthController;
use App\Services\SteamService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/home');
});

Route::get('/home', function () {
    return Inertia::render('home');
})->name('home');

Route::get('/login', function () {
    return Inertia::render('auth/login');
})->name('login');

/* Steam Auth */

Route::get('/auth/steam', [SteamAuthController::class, 'redirect'])
    ->name('steam.redirect');

Route::get('/auth/steam/callback', [SteamAuthController::class, 'callback'])
    ->name('steam.callback');

/* Protected routes */

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function (SteamService $steam) {
        $user = Auth::user();

        $games = [];

        if ($user->steam_id) {
            $games = $steam->getOwnedGames($user->steam_id);
        }

        return Inertia::render('dashboard', [
            'user' => [
                'name' => $user->name,
                'steam_id' => $user->steam_id,
                'avatar' => $user->steam_avatar_url,
            ],

            'games' => $games,
        ]);
    })->name('dashboard');

    Route::get('/backlog', function (SteamService $steam) {
        $user = Auth::user();

        $games = [];

        if ($user->steam_id) {
            $games = $steam->getOwnedGames($user->steam_id);
        }

        return Inertia::render('backlog/index', [
            'user' => [
                'name' => $user->name,
                'steam_id' => $user->steam_id,
                'avatar' => $user->steam_avatar_url,
            ],

            'games' => $games,
        ]);
    })->name('backlog.index');

    Route::get('/wishlist', function (SteamService $steam) {
        $user = Auth::user();

        $games = [];

        if ($user->steam_id) {
            $games = $steam->getWishlist($user->steam_id);
        }

        return Inertia::render('wishlist/index', [
            'user' => [
                'name' => $user->name,
                'steam_id' => $user->steam_id,
                'avatar' => $user->steam_avatar_url,
            ],

            'games' => $games,
        ]);
    })->name('wishlist.index');

    Route::get('/recommendations', function () {
        return Inertia::render('recommendations/index');
    })->name('recommendations.index');
});