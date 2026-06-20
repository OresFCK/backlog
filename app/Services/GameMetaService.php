<?php

namespace App\Services;

use App\Http\Requests\UpdateGameMetaRequest;
use App\Models\UserGameMeta;
use App\Helpers\CacheKeys;
use App\Helpers\UserCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class GameMetaService
{
    private array $emptyMetaCache = [];

    public function __construct(
        private StatusService $statuses
    ) {}

    public function storeMeta(
        UpdateGameMetaRequest $request,
        string $gameId
    ): RedirectResponse {
        $userId = Auth::id();

        UserGameMeta::updateOrCreate(
            [
                'user_id' => $userId,
                'game_id' => $gameId,
            ],
            [
                ...$request->safe()->only([
                    'note',
                    'rating',
                    'status',
                ]),

                'recommended' => $request->boolean('recommended'),
                'not_recommended' => $request->boolean('not_recommended'),
                'show_on_public_profile' => $request->boolean('show_on_public_profile'),
            ]
        );

        Cache::forget(CacheKeys::userMeta($userId, $gameId));
        UserCache::flush($userId);

        return back();
    }

    public function metaFor(string $gameId): ?UserGameMeta
    {
        $userId = Auth::id();

        return Cache::remember(
            CacheKeys::userMeta($userId, $gameId),
            now()->addMinutes(30),
            fn () => UserGameMeta::query()
                ->where('user_id', $userId)
                ->where('game_id', $gameId)
                ->first()
        );
    }

    public function existingMetaFor(string $gameId): array
    {
        $meta = $this->metaFor($gameId);

        return $meta
            ? $this->metaPayload($meta)
            : $this->emptyMeta();
    }

    public function metaPayload(?UserGameMeta $meta): array
    {
        if (! $meta) {
            return $this->emptyMeta();
        }

        $status = $meta->status ?: 'Backlog';

        return [
            'status' => $status,
            'status_color' => $this->statuses->statusColor($status),
            'note' => $meta->note,
            'rating' => $meta->rating,
            'recommended' => $meta->recommended ?? false,
            'not_recommended' => $meta->not_recommended ?? false,
            'show_on_public_profile' => $meta->show_on_public_profile ?? false,
            'updated_at' => $meta->updated_at?->diffForHumans(),
        ];
    }

    public function emptyMeta(): array
    {
        return $this->emptyMetaCache['Backlog']
            ??= [
                'status' => 'Backlog',
                'status_color' => $this->statuses->statusColor('Backlog'),
                'note' => null,
                'rating' => null,
                'recommended' => false,
                'not_recommended' => false,
                'show_on_public_profile' => false,
                'updated_at' => null,
            ];
    }
}