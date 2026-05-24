<?php

namespace App\Services;

use App\Http\Requests\StoreCustomLabelRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class StatusService
{
    public function statuses(): array
    {
        $user = Auth::user();

        if (! $user->customStatuses()->exists()) {
            $user->customStatuses()->createMany($this->defaultStatuses());
        }

        return $user
            ->customStatuses()
            ->get()
            ->map(fn ($status) => [
                'id' => $status->id,
                'name' => $status->name,
                'color' => $status->color,
            ])
            ->toArray();
    }

    public function customLabels(): array
    {
        return Auth::user()
            ->customStatuses()
            ->latest()
            ->get()
            ->map(fn ($status) => [
                'id' => $status->id,
                'title' => $status->name,
                'color' => $status->color,
            ])
            ->toArray();
    }

    public function storeStatus(StoreCustomStatusRequest $request): RedirectResponse
    {
        Auth::user()
            ->customStatuses()
            ->create($request->validated());

        return back();
    }

    public function storeCustomLabel(StoreCustomLabelRequest $request): RedirectResponse
    {
        Auth::user()
            ->customStatuses()
            ->create([
                'name' => $request->title,
                'color' => $request->color,
            ]);

        return back();
    }

    public function statusColor(?string $statusName): string
    {
        $status = collect($this->statuses())
            ->first(fn ($status) =>
                strtolower($status['name']) === strtolower($statusName ?? '')
            );

        return $status['color'] ?? '#71717a';
    }

    private function defaultStatuses(): array
    {
        return [
            [
                'name' => 'Backlog',
                'color' => '#71717a',
            ],
            [
                'name' => 'Playing',
                'color' => '#3b82f6',
            ],
            [
                'name' => 'Finished',
                'color' => '#22c55e',
            ],
            [
                'name' => 'Planned',
                'color' => '#a855f7',
            ],
            [
                'name' => 'Dropped',
                'color' => '#ef4444',
            ],
        ];
    }
}