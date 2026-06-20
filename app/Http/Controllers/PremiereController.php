<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper;
use App\Models\Game;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PremiereController extends Controller
{
    public function index(Request $request)
    {
        $games = Game::query()
            ->whereNotNull('release_date')
            ->whereNotNull('igdb_cover_url')
            ->whereDate('release_date', '>=', today())
            ->whereDate('release_date', '<=', now()->endOfYear())
            ->orderBy('release_date')
            ->get([
                'id',
                'title',
                'summary',
                'release_date',
                'slug',
                'igdb_cover_url',
        ]);

        return Inertia::render('premieres/index', [
            'user' => PayloadHelper::currentUser(),
            'games' => $games,
        ]);
    }
}