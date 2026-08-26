<?php

namespace App\Http\Controllers;

use App\Helpers\UserCache;
use App\Helpers\GameTitleNormalizer;
use App\Http\Requests\StoreBulkCustomGamesRequest;
use App\Http\Requests\UpdateCustomGameRequest;
use App\Models\CustomGame;
use App\Models\CustomListItem;
use App\Models\UserGameMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CustomGameController extends Controller
{
    public function storeBulk(StoreBulkCustomGamesRequest $request): RedirectResponse
    {
        $userId = (int) $request->user()->id;
        $titles = collect($request->validated('titles'))
            ->map(fn (string $title) => trim($title))
            ->mapWithKeys(fn (string $title) => [
                GameTitleNormalizer::normalize($title) => $title,
            ])
            ->filter(fn (string $title, string $normalized) => filled($normalized));

        $existingTitles = CustomGame::query()
            ->where('user_id', $userId)
            ->get(['title', 'normalized_title'])
            ->map(fn (CustomGame $game) => $game->normalized_title
                ?: GameTitleNormalizer::normalize($game->title))
            ->flip();

        $newGames = $titles
            ->reject(fn (string $title, string $normalized) =>
                $existingTitles->has($normalized))
            ->map(fn (string $title, string $normalized) => [
                'user_id' => $userId,
                'title' => $title,
                'normalized_title' => $normalized,
                'source' => 'manual',
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values();

        if ($newGames->isNotEmpty()) {
            DB::transaction(fn () => CustomGame::query()->insert($newGames->all()));
        }

        Cache::forget("user:{$userId}:library:custom");
        UserCache::flush($userId);

        $created = $newGames->count();
        $skipped = $titles->count() - $created;

        return back()->with(
            'success',
            "Added {$created} custom games. Skipped {$skipped} duplicates."
        );
    }

    public function update(UpdateCustomGameRequest $request, CustomGame $customGame): RedirectResponse
    {
        abort_unless($customGame->user_id === $request->user()->id, 403);

        $customGame->update($request->validated());

        Cache::forget(
            "user:{$request->user()->id}:game-details:custom-{$customGame->id}"
        );
        Cache::forget("user:{$request->user()->id}:library:custom");
        UserCache::flush((int) $request->user()->id);

        return back();
    }

    public function destroy(CustomGame $customGame): RedirectResponse
    {
        $userId = (int) auth()->id();
        abort_unless($customGame->user_id === $userId, 403);

        $gameId = "custom-{$customGame->id}";

        DB::transaction(function () use ($customGame, $userId, $gameId) {
            UserGameMeta::query()
                ->where('user_id', $userId)
                ->where('game_id', $gameId)
                ->delete();

            CustomListItem::query()
                ->where('game_id', $gameId)
                ->whereHas(
                    'list',
                    fn ($query) => $query->where('user_id', $userId)
                )
                ->delete();

            $customGame->delete();
        });

        Cache::forget(
            "user:{$userId}:game-details:custom-{$customGame->id}"
        );
        Cache::forget("user:{$userId}:library:custom");
        UserCache::flush($userId);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Custom game deleted.');
    }
}
