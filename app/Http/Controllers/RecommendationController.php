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
