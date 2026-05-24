<?php

namespace App\Services;

use App\Http\Requests\UpdateGameMetaRequest;
use App\Models\UserGameMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class GameMetaService
{
    public function __construct(
        private StatusService $statuses
    ) {}

    public function storeMeta(
        UpdateGameMetaRequest $request,
        string $gameId
    ): RedirectResponse {
        UserGameMeta::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'game_id' => $gameId,
            ],
            [
                ...$request->safe()->only([
                    'note',
                    'rating',
                    'status',
                ]),
                'recommended' => $request->boolean('recommended'),
            ]
        );

        return back();
    }

    public function metaFor(string $gameId): UserGameMeta
    {
        return UserGameMeta::firstOrCreate([
            'user_id' => Auth::id(),
            'game_id' => $gameId,
        ]);
    }

    public function existingMetaFor(string $gameId): array
    {
        $meta = UserGameMeta::where('user_id', Auth::id())
            ->where('game_id', $gameId)
            ->first();

        return $meta
            ? $this->metaPayload($meta)
            : $this->emptyMeta();
    }

    public function metaPayload(UserGameMeta $meta): array
    {
        $status = $meta->status ?? 'Backlog';

        return [
            'status' => $status,
            'status_color' => $this->statuses->statusColor($status),
            'note' => $meta->note,
            'rating' => $meta->rating,
            'recommended' => $meta->recommended ?? false,
            'updated_at' => $meta->updated_at?->diffForHumans(),
        ];
    }

    public function emptyMeta(): array
    {
        return [
            'status' => 'Backlog',
            'status_color' => $this->statuses->statusColor('Backlog'),
            'note' => null,
            'rating' => null,
            'recommended' => false,
            'updated_at' => null,
        ];
    }
}