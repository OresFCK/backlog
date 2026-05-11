<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SteamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SteamAuthController extends Controller
{
    public function redirect()
    {
        $params = [
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => 'checkid_setup',
            'openid.return_to' => route('steam.callback'),
            'openid.realm' => config('app.url'),
            'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        ];

        return redirect(
            'https://steamcommunity.com/openid/login?' . http_build_query($params)
        );
    }

    public function callback(Request $request, SteamService $steamService)
    {
        if ($request->input('openid_mode') !== 'id_res') {
            return redirect('/login');
        }

        $params = [];

        foreach ($request->query() as $key => $value) {
            $params[str_replace('_', '.', $key)] = $value;
        }

        $params['openid.mode'] = 'check_authentication';

        $response = Http::asForm()->post(
            'https://steamcommunity.com/openid/login',
            $params
        );

        if (! str_contains($response->body(), 'is_valid:true')) {
            return redirect('/login');
        }

        $claimedId = $request->input('openid_claimed_id');

        preg_match('/\/id\/(\d+)$/', $claimedId, $matches);

        $steamId = $matches[1] ?? null;

        if (! $steamId) {
            return redirect('/login');
        }

        $profile = $steamService->getPlayerSummary($steamId);

        $user = User::updateOrCreate(
            [
                'email' => 'steam_' . $steamId . '@steam.local',
            ],
            [
                'steam_id' => $steamId,
                'name' => $profile['personaname'] ?? 'Steam User',
                'password' => bcrypt(Str::random(40)),
                'steam_persona_name' => $profile['personaname'] ?? null,
                'steam_avatar_url' => $profile['avatarfull'] ?? null,
            ]
        );

        Auth::login($user, true);

        $request->session()->regenerate();

        return redirect('/wip');
    }
}