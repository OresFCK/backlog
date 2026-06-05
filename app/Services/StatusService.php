<?php

namespace App\Services;

use App\Http\Requests\StoreCustomLabelRequest;
use App\Http\Requests\StoreCustomStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class StatusService
{
    private ?array $statusesCache = null;
    private ?array $statusColorsCache = null;

    public function statuses(): array
    {
        if ($this->statusesCache !== null) {
            return $this->statusesCache;
        }

        $user = Auth::user();

        if (! $user->customStatuses()->exists()) {
            $user->customStatuses()->createMany($this->defaultStatuses());
        }

        return $this->statusesCache = $user
            ->customStatuses()
            ->get(['id', 'name', 'color'])
            ->map(fn ($status) => [
                'id' => $status->id,
                'name' => $status->name,
                'color' => $status->color,
            ])
            ->toArray();
    }

    public function customLabels(): array
    {
        return collect($this->statuses())
            ->sortByDesc('id')
            ->map(fn ($status) => [
                'id' => $status['id'],
                'title' => $status['name'],
                'color' => $status['color'],
            ])
            ->values()
            ->toArray();
    }

    public function storeStatus(StoreCustomStatusRequest $request): RedirectResponse
    {
        Auth::user()
            ->customStatuses()
            ->create($request->validated());

        $this->clearCache();

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

        $this->clearCache();

        return back();
    }

    public function statusColor(?string $statusName): string
    {
        $name = strtolower(trim($statusName ?? ''));

        if ($name === '') {
            return '#71717a';
        }

        return $this->statusColors()[$name] ?? '#71717a';
    }

    private function statusColors(): array
    {
        if ($this->statusColorsCache !== null) {
            return $this->statusColorsCache;
        }

        return $this->statusColorsCache = collect($this->statuses())
            ->mapWithKeys(fn ($status) => [
                strtolower($status['name']) => $status['color'],
            ])
            ->toArray();
    }

    private function clearCache(): void
    {
        $this->statusesCache = null;
        $this->statusColorsCache = null;
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