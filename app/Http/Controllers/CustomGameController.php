<?php

namespace App\Http\Controllers;

use App\Models\CustomGame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomGameController extends Controller
{
    public function update(Request $request, CustomGame $customGame): RedirectResponse
    {
        abort_if($customGame->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'developer' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'release_date' => ['nullable', 'date'],
            'cover_url' => ['nullable', 'string', 'max:2000'],
            'header_image_url' => ['nullable', 'string', 'max:2000'],
            'igdb_url' => ['nullable', 'string', 'max:2000'],
            'platform' => ['nullable', 'string', 'max:255'],

            'playtime_hours' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'achievements_unlocked' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'achievements_total' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        if (array_key_exists('playtime_hours', $validated)) {
            $validated['playtime_minutes'] = filled($validated['playtime_hours'])
                ? (int) round(((float) $validated['playtime_hours']) * 60)
                : null;

            unset($validated['playtime_hours']);
        }

        if (
            filled($validated['achievements_unlocked'] ?? null) &&
            filled($validated['achievements_total'] ?? null) &&
            (int) $validated['achievements_unlocked'] > (int) $validated['achievements_total']
        ) {
            return back()->withErrors([
                'achievements_unlocked' => 'Unlocked achievements cannot be greater than total achievements.',
            ]);
        }

        $customGame->update($validated);

        return back();
    }
}