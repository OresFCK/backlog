<?php

use App\Http\Controllers\Auth\SteamAuthController;
use App\Models\CustomGame;
use App\Services\SteamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

function inertiaUser(): array
{
    $user = Auth::user();

    return [
        'name' => $user->name,
        'steam_id' => $user->steam_id,
        'avatar' => $user->steam_avatar_url,
    ];
}

function ownedGames(SteamService $steam): array
{
    $user = Auth::user();

    return $user->steam_id
        ? $steam->getOwnedGames($user->steam_id)
        : [];
}

function wishlistGames(SteamService $steam): array
{
    $user = Auth::user();

    return $user->steam_id
        ? $steam->getWishlist($user->steam_id)
        : [];
}

function customGames(): array
{
    return Auth::user()
        ->customGames()
        ->get()
        ->map(fn ($game) => [
            'id' => 'custom-' . $game->id,
            'appid' => null,
            'name' => $game->title,
            'title' => $game->title,
            'publisher' => $game->publisher,
            'cover_url' => $game->cover_url,
            'playtime_forever' => 0,
            'is_custom' => true,
        ])
        ->toArray();
}

Route::get('/', fn () => redirect('/home'));

Route::get('/home', fn () => Inertia::render('home'))
    ->name('home');

Route::get('/login', fn () => Inertia::render('auth/login'))
    ->name('login');

Route::get('/auth/steam', [SteamAuthController::class, 'redirect'])
    ->name('steam.redirect');

Route::get('/auth/steam/callback', [SteamAuthController::class, 'callback'])
    ->name('steam.callback');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn (SteamService $steam) => Inertia::render('dashboard', [
        'user' => inertiaUser(),
        'games' => [
            ...ownedGames($steam),
            ...customGames(),
        ],
    ]))->name('dashboard');

    Route::get('/backlog', fn (SteamService $steam) => Inertia::render('backlog/index', [
        'user' => inertiaUser(),
        'games' => ownedGames($steam),
    ]))->name('backlog.index');

    Route::get('/wishlist', fn (SteamService $steam) => Inertia::render('wishlist/index', [
        'user' => inertiaUser(),
        'games' => wishlistGames($steam),
    ]))->name('wishlist.index');

    Route::get('/recommendations', fn (SteamService $steam) => Inertia::render('recommendations/index', [
        'user' => inertiaUser(),
        'games' => ownedGames($steam),
    ]))->name('recommendations.index');

    Route::get('/games/create', fn (SteamService $steam) => Inertia::render('games/create', [
        'user' => inertiaUser(),
        'games' => ownedGames($steam),
    ]))->name('games.create');

    Route::post('/games', function (Request $request) {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'cover_url' => ['nullable', 'string'],
        ]);

        CustomGame::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'publisher' => $validated['publisher'] ?? null,
            'cover_url' => $validated['cover_url'] ?? null,
        ]);

        return redirect('/dashboard');
    })->name('games.store');

    Route::get('/steam/search', function (SteamService $steam) {
        $query = request('q');

        return response()->json(
            $query ? $steam->searchStore($query) : []
        );
    })->name('steam.search');

    Route::get('/wip', fn (SteamService $steam) => Inertia::render('wip', [
        'user' => inertiaUser(),
        'games' => ownedGames($steam),
        'steam_error' => null,
    ]))->name('wip');
});