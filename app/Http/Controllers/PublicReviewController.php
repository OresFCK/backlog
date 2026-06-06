<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicReviewRequest;
use App\Models\ActivityLog;
use App\Models\PublicReview;
use App\Models\UserConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                'is_featured_on_profile' => $review->is_featured_on_profile,

                'game_id' => $review->game_id,
                'game_title' => $review->game_title,

                'created_at' => $review->created_at?->diffForHumans(),

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

                'is_owner' =>
                    auth()->id() === $review->user_id,

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
        $review = PublicReview::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'game_id' => $request->game_id,
            ],
            [
                'game_title' => $request->game_title,
                'title' => $request->title,
                'body' => $request->body,
                'rating' => $request->rating,

                'recommended' => $request->boolean('recommended'),
                'not_recommended' => $request->boolean('not_recommended'),

                'is_featured_on_profile' =>
                    $request->boolean('is_featured_on_profile'),

                'is_public' => true,
            ]
        );

        ActivityLog::query()->create([
            'user_id' => $request->user()->id,
            'type' => 'review_created',
            'message' => "Created review for {$review->game_title}",
            'metadata' => [
                'review_id' => $review->id,
                'game_id' => $review->game_id,
                'game_title' => $review->game_title,
                'rating' => $review->rating,
            ],
        ]);

        return back();
    }

    public function toggleFeatured(
        Request $request,
        PublicReview $review
    ): RedirectResponse {
        abort_if(
            $review->user_id !== $request->user()->id,
            403
        );

        $review->update([
            'is_featured_on_profile' =>
                ! $review->is_featured_on_profile,
        ]);

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
                            ->where('sender_id', $voterId)
                            ->where('receiver_id', $reviewAuthorId);
                    });
            })
            ->exists();
    }
}