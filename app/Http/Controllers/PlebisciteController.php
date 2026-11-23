<?php

namespace App\Http\Controllers;

use App\Models\PlebisciteVote;
use App\Support\PlebisciteCatalog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlebisciteController extends Controller
{
    public function index(Request $request)
    {
        $votes = PlebisciteVote::query()
            ->selectRaw('category_key, candidate_key, COUNT(*) as votes')
            ->groupBy('category_key', 'candidate_key')
            ->get()
            ->keyBy(fn ($vote) => $vote->category_key.':'.$vote->candidate_key);

        $myVotes = $request->user()
            ? PlebisciteVote::query()->where('user_id', $request->user()->id)->pluck('candidate_key', 'category_key')
            : collect();

        $categories = collect(PlebisciteCatalog::all())->map(function ($category) use ($votes, $myVotes) {
            $category['my_vote'] = $myVotes[$category['key']] ?? null;
            $category['candidates'] = collect($category['candidates'])->map(function ($candidate) use ($category, $votes) {
                $candidate['votes'] = (int) ($votes[$category['key'].':'.$candidate['key']]->votes ?? 0);

                return $candidate;
            })->all();
            $category['total_votes'] = collect($category['candidates'])->sum('votes');

            return $category;
        })->all();

        return Inertia::render('plebiscite/index', [
            'categories' => $categories,
            'isAuthenticated' => $request->user() !== null,
            'signInUrl' => '/login?intended='.urlencode('/plebiscite'),
            'userName' => $request->user()?->visible_name,
        ]);
    }

    public function vote(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', 'string'],
            'candidate' => ['required', 'string'],
        ]);

        $category = collect(PlebisciteCatalog::all())->firstWhere('key', $data['category']);
        abort_unless($category, 404);
        abort_unless(collect($category['candidates'])->contains('key', $data['candidate']), 422);

        PlebisciteVote::updateOrCreate(
            ['user_id' => $request->user()->id, 'category_key' => $category['key']],
            ['candidate_key' => $data['candidate']],
        );

        return back()->with('success', 'Your vote has been counted.');
    }
}
