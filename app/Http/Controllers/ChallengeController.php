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

        $challenges = Challenge::query()
            ->with('item')
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(function (Challenge $challenge) use ($user) {
                $joinedChallenge = $user->challenges()
                    ->where('challenge_id', $challenge->id)
                    ->first();

                $submission = ChallengeSubmission::query()
                    ->where('challenge_id', $challenge->id)
                    ->where('user_id', $user->id)
                    ->first();

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
        $request->validate([
            'screenshot' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ]);

        $user = $request->user();

        $user->challenges()->syncWithoutDetaching([
            $challenge->id,
        ]);

        $path = $request
            ->file('screenshot')
            ->store('challenge-submissions', 'public');

        ChallengeSubmission::updateOrCreate(
            [
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
            ],
            [
                'screenshot_path' => $path,
                'status' => 'pending',
                'admin_note' => null,
                'reviewed_at' => null,
            ]
        );

        return back();
    }
}