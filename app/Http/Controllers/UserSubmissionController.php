<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\UserSubmission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserSubmissionController extends Controller
{
    public function bug()
    {
        return Inertia::render('settings/report-bug', [
            ...Payload::pageData(app(\App\Services\SteamService::class)),
        ]);
    }

    public function suggestion()
    {
        return Inertia::render('settings/suggestion', [
            ...Payload::pageData(app(\App\Services\SteamService::class)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:bug,suggestion,admission'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request
                ->file('image')
                ->store('user-submissions', 'public');
        }

        unset($data['image']);

        UserSubmission::create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => 'new',
        ]);

        return back()->with('success', 'Submission sent.');
    }
}