<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\UserGameController;
use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/games/search', [GameController::class, 'search']);
    Route::post('/games', [GameController::class, 'store']);

    Route::get('/user-games', [UserGameController::class, 'index']);
    Route::post('/user-games', [UserGameController::class, 'store']);
    Route::patch('/user-games/{userGame}', [UserGameController::class, 'update']);
    Route::delete('/user-games/{userGame}', [UserGameController::class, 'destroy']);

    Route::get('/recommendations/now', [RecommendationController::class, 'now']);

    Route::get('/steam-test/{steamId}', function (string $steamId, SteamService $steam) {
    return response()->json([
        'profile' => $steam->getPlayerSummary($steamId),
        'games' => $steam->getOwnedGames($steamId),
    ]);
});
});