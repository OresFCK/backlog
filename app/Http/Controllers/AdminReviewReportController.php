<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PublicReviewReport;
use Illuminate\Http\RedirectResponse;

class AdminReviewReportController extends Controller
{
    public function resolve(
        PublicReviewReport $report
    ): RedirectResponse {

        $report->loadMissing([
            'reporter',
            'review',
        ]);

        $report->update([
            'status' => 'resolved',
        ]);

        if ($report->reporter) {
            ActivityLog::query()->create([
                'user_id' => $report->reporter->id,

                'type' => 'admin_report_resolved',

                'message' => 'Your review report has been resolved.',

                'metadata' => [
                    'report_id' => $report->id,

                    'review_id' => $report->public_review_id,

                    'review_title' => $report->review?->title,

                    'reason' => $report->reason,
                ],
            ]);
        }

        return back();
    }

    public function destroyReview(
        PublicReviewReport $report
    ): RedirectResponse {

        $report->loadMissing([
            'reporter',
            'review',
        ]);

        $review = $report->review;

        if ($report->reporter) {
            ActivityLog::query()->create([
                'user_id' => $report->reporter->id,

                'type' => 'admin_report_review_removed',

                'message' => 'Your report was accepted and the reported review was removed.',

                'metadata' => [
                    'report_id' => $report->id,

                    'review_id' => $report->public_review_id,

                    'review_title' => $review?->title,

                    'reason' => $report->reason,
                ],
            ]);
        }

        $review?->delete();

        $report->delete();

        return back();
    }
}