<?php

namespace App\Http\Controllers;

use App\Models\PublicReviewReport;
use Illuminate\Http\RedirectResponse;

class AdminReviewReportController extends Controller
{
    public function resolve(
        PublicReviewReport $report
    ): RedirectResponse {
        $report->update([
            'status' => 'resolved',
        ]);

        return back();
    }

    public function destroyReview(
        PublicReviewReport $report
    ): RedirectResponse {
        $report->review?->delete();

        $report->delete();

        return back();
    }
}