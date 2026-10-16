<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class NintendoService
{
    private const CLIENT_ID = '5c38e31cd085304b';

    private const APP_VERSION = '3.0.3';

    public function loginRequest(): array
    {
        $verifier = Str::random(64);
        $state = Str::random(48);
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        return [
            'verifier' => $verifier,
            'state' => $state,
            'url' => 'https://accounts.nintendo.com/connect/1.0.0/authorize?'.http_build_query([
                'state' => $state,
                'redirect_uri' => 'npf'.self::CLIENT_ID.'://auth',
                'client_id' => self::CLIENT_ID,
                'scope' => 'openid user user.mii user.email user.links[].id',
                'response_type' => 'session_token_code',
                'session_token_code_challenge' => $challenge,
                'session_token_code_challenge_method' => 'S256',
                'theme' => 'login_form',
            ]),
        ];
    }

    public function callbackParameters(string $callbackUrl): array
    {
        $url = html_entity_decode(trim($callbackUrl, " \n\r\t\v\0\"'"), ENT_QUOTES | ENT_HTML5);

        if (! str_starts_with($url, 'npf'.self::CLIENT_ID.'://auth')) {
            throw new RuntimeException('Paste the complete Nintendo “Select this person” link.');
        }

        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        parse_str(is_string($query) && $query !== '' ? $query : (string) $fragment, $parameters);

        $code = $parameters['session_token_code'] ?? null;
        $state = $parameters['state'] ?? null;

        if (! is_string($code) || $code === '') {
            throw new RuntimeException('The pasted Nintendo callback URL is invalid or expired.');
        }

        return ['code' => $code, 'state' => is_string($state) ? $state : null];
    }

    public function exchangeSessionToken(string $code, string $verifier): string
    {
        $response = $this->http()
            ->withHeaders([
                'Accept-Language' => 'en-US',
                'Accept' => 'application/json',
            ])
            ->asForm()
            ->post(
            'https://accounts.nintendo.com/connect/1.0.0/api/session_token',
            [
                'client_id' => self::CLIENT_ID,
                'session_token_code' => $code,
                'session_token_code_verifier' => $verifier,
            ]
            );

        $token = $response->successful() ? $response->json('session_token') : null;

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Nintendo rejected the login link. Start the connection again.');
        }

        return $token;
    }

    public function playHistory(string $sessionToken): array
    {
        $tokenResponse = $this->http()->post(
            'https://accounts.nintendo.com/connect/1.0.0/api/token',
            [
                'client_id' => self::CLIENT_ID,
                'session_token' => $sessionToken,
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer-session-token',
            ]
        );

        $accessToken = $tokenResponse->successful()
            ? $tokenResponse->json('access_token')
            : null;

        if (! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('The Nintendo connection has expired. Connect the account again.');
        }

        $historyResponse = $this->http()
            ->withToken($accessToken)
            ->withHeader('gentry-locale', 'en-US')
            ->get('https://app-api.znej.nintendo.com/api/v2.0/users/me/play_histories');

        if (! $historyResponse->successful()) {
            throw new RuntimeException('Nintendo play history is temporarily unavailable.');
        }

        return $historyResponse->json('playHistories') ?? [];
    }

    private function http(): PendingRequest
    {
        return Http::acceptJson()
            ->withUserAgent('com.nintendo.znej/'.self::APP_VERSION.' (iOS/26.0.1)')
            ->timeout(30);
    }
}
