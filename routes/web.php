<?php

use App\Http\Controllers\Auth\SteamAuthController;
use App\Models\CustomGame;
use App\Services\SteamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

$userPayload = fn () => [
    'name' => Auth::user()->name,
    'steam_id' => Auth::user()->steam_id,
    'avatar' => Auth::user()->steam_avatar_url,
];

$ownedGames = fn (SteamService $steam) =>
    Auth::user()->steam_id
        ? $steam->getOwnedGames(Auth::user()->steam_id)
        : [];

$wishlistGames = fn (SteamService $steam) =>
    Auth::user()->steam_id
        ? $steam->getWishlist(Auth::user()->steam_id)
        : [];

$customGames = fn () =>
    Auth::user()
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

Route::get('/', fn () => redirect('/home'));

Route::get('/home', fn () => Inertia::render('home'))
    ->name('home');

Route::get('/login', fn () => Inertia::render('auth/login'))
    ->name('login');

Route::get('/auth/steam', [SteamAuthController::class, 'redirect'])
    ->name('steam.redirect');

Route::get('/auth/steam/callback', [SteamAuthController::class, 'callback'])
    ->name('steam.callback');

Route::middleware('auth')->group(function () use (
    $userPayload,
    $ownedGames,
    $wishlistGames,
    $customGames
) {

    Route::get('/dashboard', fn (SteamService $steam) => Inertia::render('dashboard', [
        'user' => $userPayload(),

        'games' => [
            ...$ownedGames($steam),
            ...$customGames(),
        ],
    ]))->name('dashboard');

    Route::get('/backlog', fn (SteamService $steam) => Inertia::render('backlog/index', [
        'user' => $userPayload(),

        'games' => [
            ...$ownedGames($steam),
            ...$customGames(),
        ],
    ]))->name('backlog.index');

    Route::get('/wishlist', fn (SteamService $steam) => Inertia::render('wishlist/index', [
        'user' => $userPayload(),

        'games' => $wishlistGames($steam),
    ]))->name('wishlist.index');

    Route::get('/recommendations', fn (SteamService $steam) => Inertia::render('recommendations/index', [
        'user' => $userPayload(),

        'games' => [
            ...$ownedGames($steam),
            ...$customGames(),
        ],
    ]))->name('recommendations.index');

    Route::get('/games/create', fn (SteamService $steam) => Inertia::render('games/create', [
        'user' => $userPayload(),

        'games' => [
            ...$ownedGames($steam),
            ...$customGames(),
        ],
    ]))->name('games.create');

    Route::post('/games', function (Request $request) {

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'cover_url' => ['nullable', 'string', 'max:2000'],
        ]);

        CustomGame::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'publisher' => $validated['publisher'] ?? null,
            'cover_url' => $validated['cover_url'] ?? null,
        ]);

        return redirect('/dashboard');

    })->name('games.store');

    Route::get('/steam/search', function (SteamService $steam) {

        $query = request('q');

        return response()->json(
            $query
                ? $steam->searchStore($query)
                : []
        );

    })->name('steam.search');

    Route::get('/games/{game}', function (
        string $game,
        SteamService $steam
    ) use ($userPayload) {

        $user = Auth::user();

        if (str_starts_with($game, 'custom-')) {

            $customId = str_replace('custom-', '', $game);

            $customGame = $user
                ->customGames()
                ->findOrFail($customId);

            return Inertia::render('games/show', [

                'user' => $userPayload(),

                'game' => [
                    'id' => 'custom-' . $customGame->id,
                    'appid' => null,
                    'title' => $customGame->title,
                    'publisher' => $customGame->publisher,
                    'cover_url' => $customGame->cover_url,
                    'header_image' => $customGame->cover_url,
                    'description' => null,
                    'about' => null,
                    'developers' => [],
                    'publishers' => $customGame->publisher
                        ? [$customGame->publisher]
                        : [],
                    'genres' => [],
                    'screenshots' => [],
                    'platforms' => [],
                    'release_date' => null,
                    'steam_url' => null,
                    'is_custom' => true,
                ],
            ]);
        }

        $details = $steam->getAppDetails($game);

        abort_if(! $details, 404);

        return Inertia::render('games/show', [

            'user' => $userPayload(),

            'game' => [
                'id' => $game,
                'appid' => $game,

                'title' => $details['name']
                    ?? 'Unknown game',

                'publisher' => $details['publishers'][0]
                    ?? null,

                'cover_url' =>
                    $details['capsule_imagev5']
                    ?? $details['header_image']
                    ?? null,

                'header_image' =>
                    $details['header_image']
                    ?? null,

                'description' => strip_tags(
                    $details['short_description']
                    ?? ''
                ),

                'about' => strip_tags(
                    $details['about_the_game']
                    ?? ''
                ),

                'developers' =>
                    $details['developers']
                    ?? [],

                'publishers' =>
                    $details['publishers']
                    ?? [],

                'genres' => collect(
                    $details['genres']
                    ?? []
                )
                    ->pluck('description')
                    ->values()
                    ->all(),

                'screenshots' => collect(
                    $details['screenshots']
                    ?? []
                )
                    ->pluck('path_full')
                    ->values()
                    ->take(6)
                    ->all(),

                'platforms' =>
                    $details['platforms']
                    ?? [],

                'release_date' =>
                    $details['release_date']['date']
                    ?? null,

                'steam_url' =>
                    "https://store.steampowered.com/app/{$game}",

                'is_custom' => false,
            ],
        ]);

    })->name('games.show');
});