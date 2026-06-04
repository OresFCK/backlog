<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IgdbDumpService
{
    public function token(): string
    {
        return Cache::remember('igdb_token', now()->addHours(1), function () {
            $response = Http::asForm()->post('https://id.twitch.tv/oauth2/token', [
                'client_id' => config('services.igdb.client_id'),
                'client_secret' => config('services.igdb.client_secret'),
                'grant_type' => 'client_credentials',
            ]);

            $response->throw();

            return $response->json('access_token');
        });
    }

    public function getDump(string $endpoint): array
    {
        $response = Http::withHeaders([
            'Client-ID' => config('services.igdb.client_id'),
            'Authorization' => 'Bearer ' . $this->token(),
        ])->get("https://api.igdb.com/v4/dumps/{$endpoint}");

        $response->throw();

        return $response->json();
    }

    public function listDumps(): array
    {
        $response = Http::withHeaders([
            'Client-ID' => config('services.igdb.client_id'),
            'Authorization' => 'Bearer ' . $this->token(),
        ])->get('https://api.igdb.com/v4/dumps');

        $response->throw();

        return $response->json();
    }
}