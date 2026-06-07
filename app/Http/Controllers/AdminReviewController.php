<?php

namespace App\Http\Controllers;

use App\Models\PublicReview;
use Inertia\Inertia;
use Inertia\Response;

class AdminReviewController extends Controller
{
    public function show(PublicReview $review): Response
    {
        $review->loadMissing('user');

        return Inertia::render('admin/reviews/show', [
            'review' => [
                'id' => $review->id,
                'title' => $review->title,
                'body' => $review->body,
                'rating' => $review->rating,
                'recommended' => $review->recommended,
                'not_recommended' => $review->not_recommended,
                'game_title' => $review->game_title,
                'created_at' => $review->created_at?->diffForHumans(),

                'user' => [
                    'id' => $review->user?->id,
                    'name' => $review->user?->name,
                    'avatar' => $review->user?->steam_avatar_url,
                    'steam_id' => $review->user?->steam_id,
                ],
            ],
        ]);
    }
}