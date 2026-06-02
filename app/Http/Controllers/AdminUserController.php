<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->string('q')->toString();

        if (! $query) {
            return response()->json([]);
        }

        return response()->json(
            User::query()
                ->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('steam_id', 'like', "%{$query}%")
                ->limit(10)
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'steam_id' => $user->steam_id,
                    'avatar' => $user->steam_avatar_url,
                    'coins' => $user->coins ?? 0,
                    'xp' => $user->xp ?? 0,
                    'level' => $user->level ?? 1,
                ])
        );
    }

    public function logs(User $user): JsonResponse
    {
        return response()->json(
            $user->activityLogs()
                ->latest()
                ->limit(30)
                ->get()
        );
    }

    public function addCoins(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $user->increment('coins', $data['amount']);

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'admin_coins_added',
            'message' => "Admin added {$data['amount']} coins.",
            'metadata' => [
                'amount' => $data['amount'],
                'reason' => $data['reason'] ?? null,
            ],
        ]);

        return back();
    }
}