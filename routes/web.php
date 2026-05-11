<?php

use App\Http\Controllers\Auth\SteamAuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/wip', function () {
    return Inertia::render('wip');
})->name('wip');