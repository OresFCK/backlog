<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicReviewRequest;
use App\Models\PublicReview;
use App\Models\UserConnection;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PublicReviewController extends Controller
{
    public function index(): Response
    {
        $reviews = PublicReview::query()
            ->with([
                'user',
                'votes',
            ])
            ->latest()
            ->get()
            ->map(fn ($review) => [
                'id' => $review->id,
                'title' => $review->title,
                'body' => $review->body,
                'rating' => $review->rating,
                'recommended' => $review->recommended,
                'not_recommended' => $review->not_recommended,

                'can_vote' =>
                    $review->user_id !== auth()->id()
                    && $this->canVoteForReview(
                        auth()->id(),
                        $review->user_id
                    ),

                'votes_score' => $review->votes->sum('value'),

                'user_vote' => $review->votes
                    ->firstWhere('user_id', auth()->id())
                    ?->value,

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
                'not_recommended' => $request->boolean('not_recommended'),
                'is_public' => true,
            ]
        );

        return back();
    }

    private function canVoteForReview(
        int $voterId,
        int $reviewAuthorId
    ): bool {
        return UserConnection::query()
            ->where(function ($query) use ($voterId, $reviewAuthorId) {
                $query
                    ->where(function ($query) use ($voterId, $reviewAuthorId) {
                        $query
                            ->where('type', 'friend')
                            ->where('status', 'accepted')
                            ->where(function ($query) use ($voterId, $reviewAuthorId) {
                                $query
                                    ->where(function ($query) use ($voterId, $reviewAuthorId) {
                                        $query
                                            ->where('sender_id', $voterId)
                                            ->where('receiver_id', $reviewAuthorId);
                                    })
                                    ->orWhere(function ($query) use ($voterId, $reviewAuthorId) {
                                        $query
                                            ->where('sender_id', $reviewAuthorId)
                                            ->where('receiver_id', $voterId);
                                    });
                            });
                    })
                    ->orWhere(function ($query) use ($voterId, $reviewAuthorId) {
                        $query
                            ->where('type', 'follow')
                            ->where('status', 'accepted')
                            ->where('sender_id', $voterId)
                            ->where('receiver_id', $reviewAuthorId);
                    });
            })
            ->exists();
    }
}