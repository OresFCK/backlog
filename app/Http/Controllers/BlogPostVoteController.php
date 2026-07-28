<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlogPostVoteController extends Controller
{
    public function store(Request $request, BlogPost $post): RedirectResponse
    {
        abort_unless($post->is_published, 404);
        abort_if($post->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'value' => ['required', 'integer', 'in:-1,1'],
        ]);

        $post->votes()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['value' => $data['value']]
        );

        return back();
    }

    public function destroy(Request $request, BlogPost $post): RedirectResponse
    {
        $post->votes()
            ->where('user_id', $request->user()->id)
            ->delete();

        return back();
    }
}
