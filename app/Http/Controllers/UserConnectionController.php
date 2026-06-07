<?php

namespace App\Http\Controllers;

use App\Helpers\LevelSystem;
use App\Helpers\PayloadHelper as Payload;
use App\Http\Requests\StoreUserConnectionRequest;
use App\Models\User;
use App\Models\UserConnection;
use App\Services\SteamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserConnectionController extends Controller
{
    public function index(SteamService $steam): Response
    {
        return Inertia::render(
            'people/index',
            [
                ...Payload::pageData($steam),

                'connections' => $this->connections(),

                'incomingRequests' => $this->incomingRequests(),
            ]
        );
    }

    public function notifications(): JsonResponse
    {
        $incomingRequests = $this->incomingRequests();

        $adminUnreadCount = auth()->user()
            ->activityLogs()
            ->where('type', 'like', 'admin_%')
            ->whereNull('read_at')
            ->count();

        $adminNotifications = auth()->user()
            ->activityLogs()
            ->where('type', 'like', 'admin_%')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($log) => [
                'id' => 'admin_' . $log->id,
                'type' => 'admin',
                'message' => $log->message,
                'reason' => $log->metadata['reason'] ?? null,
                'created_at' => $log->created_at?->diffForHumans(),
                'read_at' => $log->read_at,
            ])
            ->values()
            ->toArray();

        return response()->json([
            'incoming_requests_count' => count($incomingRequests),

            'incoming_requests' => $incomingRequests,

            'admin_notifications_count' => $adminUnreadCount,

            'admin_notifications' => $adminNotifications,

            'total_count' => count($incomingRequests) + $adminUnreadCount,
        ]);
    }

    public function markNotificationsAsRead(): JsonResponse
    {
        auth()->user()
            ->activityLogs()
            ->where('type', 'like', 'admin_%')
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function search(
        SteamService $steam
    ): JsonResponse {
        $query = trim(
            (string) request('q')
        );

        if (! $query) {
            return response()->json([]);
        }

        $steamProfiles =
            $steam->searchPlayer($query);

        if (! count($steamProfiles)) {
            return response()->json([]);
        }

        return response()->json(
            collect($steamProfiles)
                ->map(function ($profile) {
                    $user = User::where(
                        'steam_id',
                        $profile['steam_id']
                    )->first();

                    return [
                        'exists' => filled($user),

                        'id' => $user?->id,

                        'name' =>
                            $user?->visible_name
                            ?? $profile['name'],

                        'steam_id' =>
                            $profile['steam_id'],

                        'avatar' =>
                            $user?->steam_avatar_url
                            ?? $profile['avatar'],

                        'profile_url' =>
                            $profile['profile_url']
                            ?? null,
                    ];
                })
                ->values()
                ->toArray()
        );
    }

    public function store(
        StoreUserConnectionRequest $request
    ): RedirectResponse {
        abort_if(
            (int) $request->user_id === auth()->id(),
            422
        );

        UserConnection::updateOrCreate(
            [
                'sender_id' => auth()->id(),

                'receiver_id' => $request->user_id,

                'type' => $request->type,
            ],
            [
                'status' => $request->type === 'friend'
                    ? 'pending'
                    : 'accepted',
            ]
        );

        return back();
    }

    public function accept(
        UserConnection $connection
    ): RedirectResponse {
        abort_unless(
            $connection->receiver_id === auth()->id(),
            403
        );

        abort_unless(
            $connection->type === 'friend',
            404
        );

        $connection->update([
            'status' => 'accepted',
        ]);

        return back();
    }

    public function destroy(
        UserConnection $connection
    ): RedirectResponse {
        abort_unless(
            $connection->sender_id === auth()->id()
            || $connection->receiver_id === auth()->id(),
            403
        );

        $connection->delete();

        return back();
    }

    private function currentUser(): array
    {
        $user = auth()->user();
        $level = LevelSystem::levelFromXp($user->xp ?? 0);

        return [
            'id' => $user->id,

            'name' => $user->visible_name,

            'avatar' => $user->steam_avatar_url,

            'level' => $level,

            'coins' => $user->coins ?? 0,

            'xp' => $user->xp ?? 0,

            'xp_for_current_level' => LevelSystem::xpForNextLevel($level - 1),

            'xp_for_next_level' => LevelSystem::xpForNextLevel($level),
        ];
    }

    private function incomingRequests(): array
    {
        return UserConnection::query()
            ->with('sender')
            ->where(
                'receiver_id',
                auth()->id()
            )
            ->where(
                'type',
                'friend'
            )
            ->where(
                'status',
                'pending'
            )
            ->latest()
            ->get()
            ->map(fn ($connection) => [
                'id' => $connection->id,

                'status' => $connection->status,

                'created_at' => $connection
                    ->created_at
                    ?->diffForHumans(),

                'user' => [
                    'id' => $connection->sender->id,

                    'name' => $connection->sender->visible_name,

                    'steam_id' => $connection->sender->steam_id,

                    'avatar' => $connection->sender->steam_avatar_url,
                ],
            ])
            ->toArray();
    }

    private function connections(): array
    {
        return UserConnection::query()
            ->with([
                'sender',
                'receiver',
            ])
            ->where(function ($query) {
                $query
                    ->where(
                        'sender_id',
                        auth()->id()
                    )
                    ->orWhere(
                        'receiver_id',
                        auth()->id()
                    );
            })
            ->latest()
            ->get()
            ->map(function ($connection) {
                $otherUser =
                    $connection->sender_id === auth()->id()
                    ? $connection->receiver
                    : $connection->sender;

                return [
                    'id' => $connection->id,

                    'type' => $connection->type,

                    'status' => $connection->status,

                    'is_incoming' =>
                        $connection->receiver_id === auth()->id(),

                    'created_at' => $connection
                        ->created_at
                        ?->diffForHumans(),

                    'user' => [
                        'id' => $otherUser->id,

                        'name' => $otherUser->visible_name,

                        'steam_id' => $otherUser->steam_id,

                        'avatar' => $otherUser->steam_avatar_url,
                    ],
                ];
            })
            ->toArray();
    }
}