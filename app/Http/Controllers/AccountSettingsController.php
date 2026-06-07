<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Services\SteamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountSettingsController extends Controller
{
    public function edit(SteamService $steam): Response
    {
        return Inertia::render('settings/account', [
            ...Payload::pageData($steam),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'display_name' => ['nullable', 'string', 'max:32'],
        ]);

        $request->user()->update([
            'display_name' => filled($data['display_name'] ?? null)
                ? $data['display_name']
                : null,
        ]);

        return back()->with('success', 'Nickname updated successfully.');
    }
}