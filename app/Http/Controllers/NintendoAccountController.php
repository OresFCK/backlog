<?php

namespace App\Http\Controllers;

use App\Helpers\GameTitleNormalizer;
use App\Helpers\UserCache;
use App\Models\CustomGame;
use App\Models\UserSteamGame;
use App\Services\NintendoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class NintendoAccountController extends Controller
{
    public function catalog(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'platform' => ['nullable', 'in:switch,switch2'],
        ]);
        $query = ['page' => $validated['page'] ?? 1, 'limit' => 12];

        if (! empty($validated['platform'])) {
            $query['platform'] = $validated['platform'];
        }

        try {
            $payload = Cache::remember(
                'nintendo:catalog:'.md5(json_encode($query)),
                now()->addMinutes(10),
                function () use ($query) {
                    $response = Http::acceptJson()
                        ->connectTimeout(5)
                        ->timeout(20)
                        ->retry(2, 250)
                        ->get('https://partnerzy.cenograj.pl/v1/nintendo/games', $query);

                    if (! $response->successful() || ! $response->json('success')) {
                        throw new RuntimeException('Nintendo catalog returned an invalid response.');
                    }

                    return $response->json('data');
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Nintendo catalog is temporarily unavailable. Try again in a moment.',
            ], 502);
        }

        return response()->json([
            'games' => collect($payload['games'] ?? [])->map(fn (array $game) => [
                'nintendo_title_id' => $game['nsuid'] ?? $game['applicationId'] ?? null,
                'title' => $game['title'] ?? null,
                'publisher' => $game['publisher'] ?? null,
                'developer' => $game['developer'] ?? null,
                'description' => $game['description'] ?? $game['excerpt'] ?? null,
                'release_date' => isset($game['releaseDate']) ? substr($game['releaseDate'], 0, 10) : null,
                'cover_url' => $game['images']['cover'] ?? null,
                'header_image_url' => $game['images']['banner'] ?? $game['images']['cover'] ?? null,
                'platform' => ($query['platform'] ?? null) === 'switch2'
                    || ($game['compatibility']['platformCode'] ?? null) === 'NINTENDO_SWITCH_2'
                    ? 'Nintendo Switch 2'
                    : 'Nintendo Switch',
            ])->values(),
            'pagination' => $payload['pagination'] ?? null,
        ]);
    }

    public function start(Request $request, NintendoService $nintendo): JsonResponse
    {
        $login = $nintendo->loginRequest();
        $request->session()->put('nintendo_oauth', [
            'verifier' => $login['verifier'],
            'state' => $login['state'],
        ]);

        return response()->json(['login_url' => $login['url']]);
    }

    public function connect(Request $request, NintendoService $nintendo): RedirectResponse
    {
        $data = $request->validate(['callback_url' => ['required', 'string', 'max:4000']]);
        $oauth = $request->session()->pull('nintendo_oauth');

        if (! is_array($oauth) || empty($oauth['verifier'])) {
            return back()->withErrors(['callback_url' => 'Start Nintendo login again before pasting the link.']);
        }

        try {
            $callback = $nintendo->callbackParameters($data['callback_url']);

            if (! hash_equals((string) $oauth['state'], (string) $callback['state'])) {
                throw new RuntimeException('The Nintendo login state does not match. Start again.');
            }

            $token = $nintendo->exchangeSessionToken($callback['code'], $oauth['verifier']);
            $request->user()->nintendoAccount()->updateOrCreate([], ['session_token' => $token]);
            $result = $this->import($request, $nintendo);

            return back()->with('success', "Nintendo connected. Added {$result['created']} games and updated {$result['updated']}.");
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'callback_url' => $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'Nintendo connection failed. Please try again.',
            ]);
        }
    }

    public function sync(Request $request, NintendoService $nintendo): RedirectResponse
    {
        if (! $request->user()->nintendoAccount) {
            return back()->withErrors(['nintendo' => 'Connect a Nintendo account first.']);
        }

        try {
            $result = $this->import($request, $nintendo);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'nintendo' => $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'Nintendo sync failed. Please try again.',
            ]);
        }

        return back()->with('success', "Nintendo sync complete. Added {$result['created']} games and updated {$result['updated']}.");
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->nintendoAccount?->delete();

        return back()->with('success', 'Nintendo account disconnected. Imported games were kept.');
    }

    private function import(Request $request, NintendoService $nintendo): array
    {
        $user = $request->user();
        $account = $user->nintendoAccount;
        $accessToken = $nintendo->accessToken($account->session_token);
        $history = collect($nintendo->playHistory($accessToken))
            ->filter(fn ($item) => is_array($item));
        $knownTitles = CustomGame::query()->where('user_id', $user->id)->pluck('normalized_title')
            ->merge(UserSteamGame::query()->where('user_id', $user->id)->pluck('name')->map(fn ($title) => GameTitleNormalizer::normalize($title)))
            ->filter()->flip();
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($history, $user, $knownTitles, &$created, &$updated) {
            foreach ($history as $item) {
                $title = trim((string) ($item['titleName'] ?? ''));
                $titleId = trim((string) ($item['titleId'] ?? ''));
                $normalized = GameTitleNormalizer::normalize($title);

                if ($title === '' || $titleId === '') {
                    continue;
                }

                $game = CustomGame::query()->where('user_id', $user->id)
                    ->where('nintendo_title_id', $titleId)->first();

                if (! $game && $knownTitles->has($normalized)) {
                    continue;
                }

                $values = [
                    'title' => $title,
                    'normalized_title' => $normalized,
                    'nintendo_title_id' => $titleId,
                    'source' => 'nintendo',
                    'platform' => $this->platform($item['platform'] ?? $item['deviceType'] ?? null),
                    'cover_url' => $item['imageUrl'] ?? null,
                    'header_image_url' => $item['imageUrl'] ?? null,
                    'playtime_minutes' => max(0, (int) ($item['totalPlayedMinutes'] ?? 0)),
                    'first_played_at' => $this->historyTimestamp($item['firstPlayedAt'] ?? null),
                    'last_played_at' => $this->historyTimestamp($item['lastPlayedAt'] ?? null),
                ];

                if ($game) {
                    $game->update($values);
                    $updated++;
                } else {
                    $user->customGames()->create($values);
                    $knownTitles->put($normalized, true);
                    $created++;
                }
            }
        });

        $titleIds = $history
            ->map(fn (array $item) => trim((string) ($item['titleId'] ?? '')))
            ->filter()
            ->unique()
            ->values();
        $nintendoGames = CustomGame::query()
            ->where('user_id', $user->id)
            ->whereIn('nintendo_title_id', $titleIds)
            ->get(['id', 'nintendo_title_id']);

        foreach ($nintendoGames as $game) {
            $offset = 0;

            do {
                $dailyRows = $nintendo->dailyPlayHistory($accessToken, $game->nintendo_title_id, $offset);
                $records = collect($dailyRows)->filter(fn ($row) => is_array($row))
                    ->map(function (array $row) use ($game) {
                        $date = $row['playedDate'] ?? $row['date'] ?? $row['playedAt'] ?? null;
                        $minutes = $row['totalPlayedMinutes'] ?? $row['playtimeMinutes'] ?? $row['playedMinutes'] ?? null;

                        if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}/', $date) || ! is_numeric($minutes)) {
                            return null;
                        }

                        return [
                            'custom_game_id' => $game->id,
                            'played_on' => substr($date, 0, 10),
                            'playtime_minutes' => max(0, (int) $minutes),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    })->filter()->values()->all();

                if ($records !== []) {
                    DB::table('nintendo_daily_playtimes')->upsert(
                        $records,
                        ['custom_game_id', 'played_on'],
                        ['playtime_minutes', 'updated_at']
                    );
                }

                $offset += 100;
            } while (count($dailyRows) === 100 && $offset < 100000);
        }

        $account->update(['last_synced_at' => now()]);
        Cache::forget("user:{$user->id}:library:custom");
        Cache::forget("user:{$user->id}:library:all");
        UserCache::flush((int) $user->id);

        return compact('created', 'updated');
    }

    private function platform(?string $platform): string
    {
        return str_contains(strtolower((string) $platform), '2')
            ? 'Nintendo Switch 2'
            : 'Nintendo Switch';
    }

    private function historyTimestamp(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return now()->parse($value)->toDateTimeString();
        } catch (Throwable) {
            return null;
        }
    }
}
