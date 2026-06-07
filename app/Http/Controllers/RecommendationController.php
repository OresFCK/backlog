<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Services\RecommendationService;
use App\Services\SteamService;
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

                'backlogRecommendations' =>
                    $this->recommendations->backlogRecommendations(),

                'steamRecommendations' =>
                    $this->recommendations->steamRecommendations(),

                'friendsRanking' =>
                    $this->recommendations->friendsRanking(),

                'globalRanking' =>
                    $this->recommendations->globalRanking(),
            ]
        );
    }
}