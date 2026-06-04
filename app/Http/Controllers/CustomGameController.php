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
        ]);

        $customGame->update($validated);

        return back();
    }
}