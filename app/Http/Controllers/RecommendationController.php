<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationController extends Controller
{
    public function __construct(
        private RecommendationService $recommendations
    ) {}

    public function index(): Response
    {
        return Inertia::render(
            'recommendations/index',
            [
                'backlogRecommendations' =>
                    $this->recommendations->backlogRecommendations(),

                'steamRecommendations' =>
                    $this->recommendations->steamRecommendations(),

                'friendsRanking' =>
                    $this->recommendations->friendsRanking(),

                'globalRanking' =>
                    $this->recommendations->globalRanking(),

                'user' => [
                    'name' => auth()->user()->name,
                    'avatar' => auth()->user()->steam_avatar_url,
                ],
            ]
        );
    }
}