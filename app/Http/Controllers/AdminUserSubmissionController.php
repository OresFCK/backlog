<?php

namespace App\Http\Controllers;

use App\Models\UserSubmission;
use Illuminate\Support\Facades\Storage;

class AdminUserSubmissionController extends Controller
{
    public function resolve(UserSubmission $submission)
    {
        $submission->update([
            'status' => 'resolved',
        ]);

        return back();
    }

    public function destroy(UserSubmission $submission)
    {
        if ($submission->image_path) {
            Storage::disk('public')->delete($submission->image_path);
        }

        $submission->delete();

        return back();
    }
}