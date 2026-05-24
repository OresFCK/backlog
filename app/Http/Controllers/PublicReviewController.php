<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicReviewRequest;
use App\Models\PublicReview;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PublicReviewController extends Controller
{
    public function index(): Response
    {
        $reviews = PublicReview::query()
            ->with('user')
            ->latest()
            ->get()
            ->map(fn ($review) => [
                'id' => $review->id,
                'title' => $review->title,
                'body' => $review->body,
                'rating' => $review->rating,
                'recommended' => $review->recommended,
                'game_id' => $review->game_id,
                'created_at' => $review->created_at?->diffForHumans(),

                'user' => [
                    'name' => $review->user?->name,
                    'avatar' => $review->user?->steam_avatar_url,
                ],
            ])
            ->values()
            ->toArray();

        return Inertia::render(
            'reviews/index',
            [
                'reviews' => $reviews,

                'user' => [
                    'name' => auth()->user()->name,
                    'avatar' => auth()->user()->steam_avatar_url,
                ],
            ]
        );
    }

    public function store(
        StorePublicReviewRequest $request
    ): RedirectResponse {
        PublicReview::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'game_id' => $request->game_id,
            ],
            [
                'title' => $request->title,
                'body' => $request->body,
                'rating' => $request->rating,
                'recommended' => $request->boolean('recommended'),
                'is_public' => true,
            ]
        );

        return back();
    }
}