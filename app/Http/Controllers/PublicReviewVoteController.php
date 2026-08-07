<?php

namespace App\Http\Controllers;

use App\Models\PublicReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicReviewVoteController extends Controller
{
    public function store(
        Request $request,
        PublicReview $review
    ): RedirectResponse {
        abort_unless($review->is_public, 404);

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
        abort_unless($review->is_public, 404);

        abort_if($review->user_id === $request->user()->id, 403);

        $review->votes()
            ->where('user_id', $request->user()->id)
            ->delete();

        return back();
    }

}
