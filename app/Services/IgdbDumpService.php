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

    public function searchGames(string $query, int $limit = 15): array
    {
        if (
            blank(config('services.igdb.client_id'))
            || blank(config('services.igdb.client_secret'))
        ) {
            return [];
        }

        $escapedQuery = addcslashes($query, "\\\"");
        $limit = max(1, min($limit, 50));
        $response = Http::withHeaders([
            'Client-ID' => config('services.igdb.client_id'),
            'Authorization' => 'Bearer '.$this->token(),
            'Accept' => 'application/json',
        ])->withBody(
            'search "'.$escapedQuery.'"; '
            .'fields id,name,slug,summary,first_release_date,cover.image_id,'
            .'screenshots.image_id,involved_companies.company.name,'
            .'involved_companies.developer,involved_companies.publisher; '
            .'limit '.$limit.';',
            'text/plain'
        )->post('https://api.igdb.com/v4/games');

        $response->throw();

        return $response->json();
    }
}
