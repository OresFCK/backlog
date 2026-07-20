<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Helpers\GameTitleNormalizer;
use App\Http\Requests\StorePublicReviewRequest;
use App\Models\ActivityLog;
use App\Models\Game;
use App\Models\CustomGame;
use App\Models\PublicReview;
use App\Models\UserConnection;
use App\Models\UserGameMeta;
use App\Services\SteamService;
use App\Services\ReviewGameResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicReviewController extends Controller
{
    public function __construct(
        private ReviewGameResolver $reviewGameResolver
    ) {}

    public function index(SteamService $steam): Response
    {
        return $this->renderReviewsPage($steam);
    }

    public function mine(SteamService $steam): Response
    {
        return $this->renderReviewsPage(
            $steam,
            auth()->id(),
            'My Reviews',
            'All public reviews written by you.',
            true
        );
    }

    private function renderReviewsPage(
        SteamService $steam,
        ?int $userId = null,
        string $pageTitle = 'Reviews',
        string $pageDescription = 'Public reviews from your community.',
        bool $isMyReviews = false
    ): Response {
        $reviewModels = PublicReview::query()
            ->with([
                'user',
                'votes',
            ])
            ->where('is_public', true)
            ->when(
                $userId,
                fn ($query) => $query->where('user_id', $userId)
            )
            ->latest()
            ->get();

        $gamesByReview = $this->reviewGameResolver->resolveMany($reviewModels);

        $reviews = $reviewModels
            ->map(fn ($review) => $this->reviewData(
                $review,
                $gamesByReview->get($review->id)
            ))
            ->values()
            ->toArray();

        return Inertia::render(
            'reviews/index',
            [
                ...Payload::pageData($steam),
                'reviews' => $reviews,
                'pageTitle' => $pageTitle,
                'pageDescription' => $pageDescription,
                'isMyReviews' => $isMyReviews,
            ]
        );
    }

    public function show(PublicReview $review): Response
    {
        abort_unless($review->is_public, 404);

        $review->loadMissing(['user', 'votes']);

        $resolvedGame = $this->reviewGameResolver
            ->resolveMany(collect([$review]))
            ->get($review->id);

        $reviewData = $this->reviewData($review, $resolvedGame);
        $plainBody = trim(preg_replace('/\s+/', ' ', $review->body ?? ''));
        $description = mb_strlen($plainBody) > 155
            ? mb_substr($plainBody, 0, 152).'...'
            : $plainBody;

        return Inertia::render('reviews/show', [
            'review' => $reviewData,
            'seo' => [
                'title' => ($review->title ?: 'Game review')
                    .' — '.$reviewData['game_title'].' | Curator.gg',
                'description' => $description,
                'url' => $reviewData['share_url'],
                'image' => $resolvedGame?->header_image_url
                    ?: $resolvedGame?->cover_url
                    ?: $resolvedGame?->igdb_cover_url
                    ?: $reviewData['screenshot_url']
                    ?: asset('og-image.jpg'),
                'image_alt' => $reviewData['game_title'].' review by '
                    .($reviewData['user']['name'] ?: 'a Curator.gg user'),
            ],
        ]);
    }

    private function reviewData(
        PublicReview $review,
        ?Game $resolvedGame = null
    ): array
    {
        return [
            'id' => $review->id,
            'title' => $review->title,
            'body' => $review->body,
            'rating' => $review->rating,
            'platform' => $review->platform,
            'screenshot_url' => $review->screenshot_path
                ? url(Storage::url($review->screenshot_path))
                : null,
            'recommended' => $review->recommended,
            'not_recommended' => $review->not_recommended,
            'is_featured_on_profile' => $review->is_featured_on_profile,
            'time_to_beat_hours' => $review->time_to_beat_minutes
                ? round($review->time_to_beat_minutes / 60, 2)
                : null,
            'game_id' => $resolvedGame?->id ?: $review->game_id,
            'game_title' => $resolvedGame?->title ?: $review->game_title,
            'game_slug' => $resolvedGame?->slug,
            'created_at' => $review->created_at?->diffForHumans(),
            'share_url' => route('reviews.public.show', $review),
            'can_vote' => auth()->check()
                && $review->user_id !== auth()->id()
                && $this->canVoteForReview(auth()->id(), $review->user_id),
            'votes_score' => $review->votes->sum('value'),
            'user_vote' => auth()->check()
                ? $review->votes->firstWhere('user_id', auth()->id())?->value
                : null,
            'is_owner' => auth()->id() === $review->user_id,
            'user' => [
                'name' => $review->user?->visible_name,
                'avatar' => $review->user?->steam_avatar_url,
            ],
        ];
    }

    public function store(
        StorePublicReviewRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        [$game, $sourceGameId] = $this->resolveReviewedGame(
            (string) $data['game_id'],
            $request->user()->id
        );

        $existingReview = PublicReview::query()
            ->where('user_id', $request->user()->id)
            ->where('game_id', $game->id)
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
                'game_id' => $game->id,
            ],
            [
                'source' => $game->source,
                'source_game_id' => $sourceGameId,

                'game_title' => $game->title,
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

                'time_to_beat_minutes' => filled($data['time_to_beat_hours'] ?? null)
                    ? (int) round(((float) $data['time_to_beat_hours']) * 60)
                    : null,
            ]
        );

        UserGameMeta::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'game_id' => $sourceGameId,
            ],
            [
                'status' => 'Finished',
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

    private function resolveReviewedGame(
        string $gameIdentifier,
        int $userId
    ): array {
        if (ctype_digit($gameIdentifier)) {
            $game = Game::query()->findOrFail((int) $gameIdentifier);

            return [
                $game,
                (string) (
                    $game->steam_app_id
                    ?? $game->igdb_id
                    ?? $game->id
                ),
            ];
        }

        abort_unless(
            preg_match('/^custom:([1-9][0-9]*)$/', $gameIdentifier, $matches),
            422
        );

        $customGame = CustomGame::query()
            ->where('user_id', $userId)
            ->findOrFail((int) $matches[1]);

        $normalizedTitle = GameTitleNormalizer::normalize($customGame->title);

        $game = Game::query()
            ->when(
                $customGame->igdb_id,
                fn ($query, $igdbId) => $query->where('igdb_id', $igdbId),
                fn ($query) => $query->where('normalized_title', $normalizedTitle)
            )
            ->first();

        if (! $game) {
            $game = Game::query()->create([
                'igdb_id' => $customGame->igdb_id,
                'title' => $customGame->title,
                'normalized_title' => $normalizedTitle,
                'summary' => $customGame->description,
                'source' => $customGame->source ?? 'manual',
                'cover_url' => $customGame->cover_url,
                'header_image_url' => $customGame->header_image_url,
                'release_date' => $customGame->release_date,
            ]);
        }

        return [$game, 'custom:' . $customGame->id];
    }
}
