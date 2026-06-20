<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicGameSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        return response()->json(
            Game::query()
                ->whereNotNull('slug')
                ->where(function ($builder) use ($query) {
                    $builder
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%")
                        ->orWhere('steam_app_id', 'like', "%{$query}%");
                })
                ->orderBy('title')
                ->limit(8)
                ->get()
                ->map(fn (Game $game) => [
                    'id' => $game->id,
                    'title' => $game->title,
                    'slug' => $game->slug,
                    'steam_app_id' => $game->steam_app_id,
                    'cover_url' => $game->cover_url,
                ])
                ->values()
        );
    }
}