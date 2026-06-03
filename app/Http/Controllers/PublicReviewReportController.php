<?php

namespace App\Http\Controllers;

use App\Models\PublicReview;
use App\Models\PublicReviewReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicReviewReportController extends Controller
{
    public function store(
        Request $request,
        PublicReview $review
    ): RedirectResponse {
        abort_if(
            $review->user_id === $request->user()->id,
            403
        );

        $data = $request->validate([
            'reason' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        PublicReviewReport::updateOrCreate(
            [
                'public_review_id' => $review->id,
                'reporter_id' => $request->user()->id,
            ],
            [
                'reason' => $data['reason'] ?? null,
                'status' => 'open',
            ]
        );

        return back();
    }
}