<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Services\SteamService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountSettingsController extends Controller
{
    public function edit(SteamService $steam)
    {
        return Inertia::render('settings/account', [
            ...Payload::pageData($steam),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'display_name' => ['nullable', 'string', 'max:32'],
        ]);

        $request->user()->update([
            'display_name' => filled($data['display_name'])
                ? $data['display_name']
                : null,
        ]);

        return back();
    }
}