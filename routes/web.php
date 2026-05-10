<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/dashboard');
});

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/backlog', function () {
        return Inertia::render('Backlog/Index');
    })->name('backlog.index');

    Route::get('/wishlist', function () {
        return Inertia::render('Wishlist/Index');
    })->name('wishlist.index');

    Route::get('/recommendations', function () {
        return Inertia::render('Recommendations/Index');
    })->name('recommendations.index');

