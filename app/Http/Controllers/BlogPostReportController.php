<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogPostReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlogPostReportController extends Controller
{
    public function store(Request $request, BlogPost $post): RedirectResponse
    {
        abort_unless($post->is_published, 404);
        abort_if($post->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        BlogPostReport::query()->updateOrCreate(
            [
                'blog_post_id' => $post->id,
                'reporter_id' => $request->user()->id,
            ],
            [
                'reason' => $data['reason'],
                'status' => 'open',
            ]
        );

        return back()->with('success', 'Post reported.');
    }
}
