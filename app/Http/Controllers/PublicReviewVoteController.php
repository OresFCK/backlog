<?php

namespace App\Http\Controllers;

use App\Models\PublicReview;
use App\Models\UserConnection;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PublicReviewVoteController extends Controller
{
    public function store(
        Request $request,
        PublicReview $review
    ): RedirectResponse {

        $data = $request->validate([
            'value' => [
                'required',
                'integer',
                'in:-1,1',
            ],
        ]);

        abort_if(
            $review->user_id === $request->user()->id,
            403
        );

        abort_unless(
            $this->canVoteForReview(
                $request->user()->id,
                $review->user_id
            ),
            403
        );

        $review->votes()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
            ],
            [
                'value' => $data['value'],
            ]
        );

        return back();
    }

    public function destroy(
        Request $request,
        PublicReview $review
    ): RedirectResponse {

        abort_unless(
            $this->canVoteForReview(
                $request->user()->id,
                $review->user_id
            ),
            403
        );

        $review->votes()
            ->where(
                'user_id',
                $request->user()->id
            )
            ->delete();

        return back();
    }

    private function canVoteForReview(
        int $voterId,
        int $reviewAuthorId
    ): bool {

        return UserConnection::query()

            ->where(function ($query) use (
                $voterId,
                $reviewAuthorId
            ) {

                $query
                    ->where(function ($query) use (
                        $voterId,
                        $reviewAuthorId
                    ) {

                        $query
                            ->where(
                                'type',
                                'friend'
                            )

                            ->where(
                                'status',
                                'accepted'
                            )

                            ->where(function ($query) use (
                                $voterId,
                                $reviewAuthorId
                            ) {

                                $query
                                    ->where(function ($query) use (
                                        $voterId,
                                        $reviewAuthorId
                                    ) {

                                        $query
                                            ->where(
                                                'sender_id',
                                                $voterId
                                            )

                                            ->where(
                                                'receiver_id',
                                                $reviewAuthorId
                                            );
                                    })

                                    ->orWhere(function ($query) use (
                                        $voterId,
                                        $reviewAuthorId
                                    ) {

                                        $query
                                            ->where(
                                                'sender_id',
                                                $reviewAuthorId
                                            )

                                            ->where(
                                                'receiver_id',
                                                $voterId
                                            );
                                    });
                            });
                    })

                    ->orWhere(function ($query) use (
                        $voterId,
                        $reviewAuthorId
                    ) {

                        $query
                            ->where(
                                'type',
                                'follow'
                            )

                            ->where(
                                'status',
                                'accepted'
                            )

                            ->where(
                                'sender_id',
                                $voterId
                            )

                            ->where(
                                'receiver_id',
                                $reviewAuthorId
                            );
                    });
            })

            ->exists();
    }
}