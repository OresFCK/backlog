<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\Challenge;
use App\Models\ChallengeSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ChallengeController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $joinedChallenges = $user
            ->challenges()
            ->get(['challenges.id'])
            ->keyBy('id');

        $submissions = ChallengeSubmission::query()
            ->where('user_id', $user->id)
            ->get([
                'challenge_id',
                'status',
                'admin_note',
            ])
            ->keyBy('challenge_id');

        $challenges = Challenge::query()
            ->with('item:id,name,type')
            ->where('is_active', true)
            ->latest()
            ->get([
                'id',
                'shop_item_id',
                'title',
                'description',
                'game_name',
                'reward_xp',
                'reward_coins',
            ])
            ->map(function (Challenge $challenge) use ($joinedChallenges, $submissions) {
                $joinedChallenge = $joinedChallenges->get($challenge->id);
                $submission = $submissions->get($challenge->id);

                return [
                    'id' => $challenge->id,
                    'title' => $challenge->title,
                    'description' => $challenge->description,
                    'game_name' => $challenge->game_name,
                    'reward_xp' => $challenge->reward_xp,
                    'reward_coins' => $challenge->reward_coins,
                    'joined' => (bool) $joinedChallenge,
                    'completed' => (bool) $joinedChallenge?->pivot?->completed_at,
                    'submission_status' => $submission?->status,
                    'admin_note' => $submission?->admin_note,

                    'item' => $challenge->item ? [
                        'id' => $challenge->item->id,
                        'name' => $challenge->item->name,
                        'type' => $challenge->item->type,
                    ] : null,
                ];
            });

        return Inertia::render('challenges/index', [
            'user' => Payload::currentUser(),
            'challenges' => $challenges,
        ]);
    }

    public function join(Challenge $challenge): RedirectResponse
    {
        Auth::user()->challenges()->syncWithoutDetaching([
            $challenge->id,
        ]);

        return back();
    }

    public function submit(Request $request, Challenge $challenge): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['nullable', 'string', 'max:1000'],
            'screenshots' => ['required', 'array', 'min:1', 'max:5'],
            'screenshots.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $user = $request->user();

        $user->challenges()->syncWithoutDetaching([
            $challenge->id,
        ]);

        $paths = [];

        foreach ($request->file('screenshots', []) as $screenshot) {
            $paths[] = $screenshot->store('challenge-submissions', 'public');
        }

        ChallengeSubmission::query()->updateOrCreate(
            [
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
            ],
            [
                'screenshot_path' => $paths[0] ?? null,
                'screenshot_paths' => $paths,
                'description' => $data['description'] ?? null,
                'status' => 'pending',
                'admin_note' => null,
                'reviewed_at' => null,
            ]
        );

        return back();
    }
}