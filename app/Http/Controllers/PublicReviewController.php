<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Http\Requests\StorePublicReviewRequest;
use App\Models\ActivityLog;
use App\Models\PublicReview;
use App\Models\UserConnection;
use App\Services\SteamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicReviewController extends Controller
{
    public function index(SteamService $steam): Response
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
                'platform' => $review->platform,
                'screenshot_url' => $review->screenshot_path
                    ? Storage::url($review->screenshot_path)
                    : null,
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
                    'name' => $review->user?->visible_name,
                    'avatar' => $review->user?->steam_avatar_url,
                ],
            ])
            ->values()
            ->toArray();

        return Inertia::render(
            'reviews/index',
            [
                ...Payload::pageData($steam),
                'reviews' => $reviews,
            ]
        );
    }

    public function store(
        StorePublicReviewRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $existingReview = PublicReview::query()
            ->where('user_id', $request->user()->id)
            ->where('game_id', $request->game_id)
            ->first();

        if ($request->hasFile('screenshot')) {
            if ($existingReview?->screenshot_path) {
                Storage::disk('public')->delete(
                    $existingReview->screenshot_path
                );
            }

            $data['screenshot_path'] = $request
                ->file('screenshot')
                ->store('review-screenshots', 'public');
        }

        unset($data['screenshot']);

        $review = PublicReview::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'game_id' => $request->game_id,
            ],
            [
                'game_title' => $data['game_title'],
                'title' => $data['title'],
                'body' => $data['body'],
                'rating' => $data['rating'],
                'platform' => $data['platform'] ?? null,

                'screenshot_path' => $data['screenshot_path']
                    ?? $existingReview?->screenshot_path,

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
                'platform' => $review->platform,
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