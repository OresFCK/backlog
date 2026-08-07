<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\Game;
use App\Services\IgdbImageService;
use App\Services\RecommendationService;
use App\Services\SteamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationController extends Controller
{
    public function __construct(
        private RecommendationService $recommendations
    ) {}

    public function index(SteamService $steam): Response
    {
        return Inertia::render(
            'recommendations/index',
            [
                ...Payload::pageData($steam),

                'backlogRecommendations' => $this->recommendations->backlogRecommendations(),

                'steamRecommendations' => $this->recommendations->steamRecommendations(),

                'friendsRanking' => $this->recommendations->friendsRanking(),

                'globalRanking' => $this->recommendations->globalRanking(),
            ]
        );
    }

    public function shared(Request $request): Response
    {
        $ids = collect(explode(',', (string) $request->query('games')))
            ->filter(fn (string $id) => ctype_digit($id))
            ->unique()
            ->take(3)
            ->values();

        abort_if($ids->isEmpty(), 404);

        $games = Game::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy(fn (Game $game) => (string) $game->id);

        $orderedGames = $ids
            ->map(fn (string $id) => $games->get($id))
            ->filter()
            ->map(fn (Game $game) => [
                'id' => $game->id,
                'title' => $game->title,
                'url' => route('games.public.show', $game),
                'cover_image_url' => filled($game->steam_app_id)
                    ? "https://cdn.cloudflare.steamstatic.com/steam/apps/{$game->steam_app_id}/library_600x900.jpg"
                    : ($game->cover_url ?: $game->igdb_cover_url),
                'image_fallback_url' => filled($game->steam_app_id)
                    ? "https://cdn.cloudflare.steamstatic.com/steam/apps/{$game->steam_app_id}/header.jpg"
                    : ($game->header_image_url ?: $game->igdb_cover_url),
            ])
            ->values();

        abort_if($orderedGames->isEmpty(), 404);

        $moods = ['short', 'immersive', 'chill', 'friends', 'surprise'];
        $mood = in_array($request->query('mood'), $moods, true)
            ? $request->query('mood')
            : null;

        return Inertia::render('recommendations/shared', [
            'games' => $orderedGames,
            'mood' => $mood,
        ]);
    }

    public function artwork(
        string $gameId,
        SteamService $steam,
        IgdbImageService $igdbImages,
        Request $request
    ): JsonResponse {
        abort_unless(ctype_digit($gameId), 404);

        $game = Game::query()->findOrFail((int) $gameId);
        $igdbArtwork = $game->igdb_id
            ? $igdbImages->forGame((int) $game->igdb_id)
            : [];

        if (filled($igdbArtwork['header_image_url'] ?? null)) {
            return response()->json([
                'url' => $igdbArtwork['header_image_url'],
                'source' => 'igdb',
            ]);
        }

        $steamAppId = $game->steam_app_id
            ?: $request->string('steam_app_id')->toString();

        if (! ctype_digit((string) $steamAppId)) {
            return response()->json(['url' => null, 'source' => null]);
        }

        $details = $steam->getAppDetails($steamAppId);
        $screenshots = collect($details['screenshots'] ?? [])
            ->pluck('path_full')
            ->filter()
            ->values();

        return response()->json([
            'url' => $screenshots->first()
                ?: ($details['background_raw'] ?? $details['background'] ?? null),
            'source' => 'steam-screenshot',
        ]);
    }
}
