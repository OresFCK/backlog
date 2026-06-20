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
        return Inertia::render('people/index', [
            ...Payload::pageData($steam),

            'connections' => $this->connections(),
            'incomingRequests' => $this->incomingRequests(),
        ]);
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

    public function search(SteamService $steam): JsonResponse
    {
        $query = trim((string) request('q'));

        if (! $query) {
            return response()->json([]);
        }

        $currentUserId = auth()->id();
        $currentSteamId = auth()->user()->steam_id;

        $steamProfiles = collect($steam->searchPlayer($query));

        $curatorUsers = User::query()
            ->where('id', '!=', $currentUserId)
            ->where(function ($builder) use ($query) {
                $builder
                    ->where('display_name', 'like', "%{$query}%")
                    ->orWhere('steam_persona_name', 'like', "%{$query}%")
                    ->orWhere('steam_id', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(fn (User $user) => [
                'steam_id' => $user->steam_id,
                'name' => $user->visible_name,
                'avatar' => $user->steam_avatar_url,
                'profile_url' => null,
                'curator_priority' => 1,
            ]);

        $steamProfiles = $steamProfiles
            ->filter(fn ($profile) =>
                (string) $profile['steam_id'] !== (string) $currentSteamId
            )
            ->map(fn ($profile) => [
                'steam_id' => $profile['steam_id'],
                'name' => $profile['name'],
                'avatar' => $profile['avatar'],
                'profile_url' => $profile['profile_url'] ?? null,
                'curator_priority' => 0,
            ]);

        return response()->json(
            $curatorUsers
                ->merge($steamProfiles)
                ->unique('steam_id')
                ->map(function ($profile) use ($currentUserId) {
                    $user = User::where('steam_id', $profile['steam_id'])->first();

                    $friendConnection = $user
                        ? UserConnection::query()
                            ->where('type', 'friend')
                            ->where(function ($query) use ($currentUserId, $user) {
                                $query
                                    ->where(function ($query) use ($currentUserId, $user) {
                                        $query
                                            ->where('sender_id', $currentUserId)
                                            ->where('receiver_id', $user->id);
                                    })
                                    ->orWhere(function ($query) use ($currentUserId, $user) {
                                        $query
                                            ->where('sender_id', $user->id)
                                            ->where('receiver_id', $currentUserId);
                                    });
                            })
                            ->first()
                        : null;

                    $followConnection = $user
                        ? UserConnection::query()
                            ->where('type', 'follow')
                            ->where('sender_id', $currentUserId)
                            ->where('receiver_id', $user->id)
                            ->first()
                        : null;

                    return [
                        'exists' => filled($user),
                        'id' => $user?->id,
                        'name' => $user?->visible_name ?? $profile['name'],
                        'steam_id' => $profile['steam_id'],
                        'avatar' => $user?->steam_avatar_url ?? $profile['avatar'],
                        'profile_url' => $profile['profile_url'] ?? null,
                        'curator_priority' => filled($user)
                            ? 1
                            : $profile['curator_priority'],

                        'friend_status' => $friendConnection?->status,
                        'is_friend' => $friendConnection?->status === 'accepted',
                        'friend_request_pending' => $friendConnection?->status === 'pending',

                        'is_following' => filled($followConnection),
                    ];
                })
                ->sortByDesc('curator_priority')
                ->groupBy(fn ($profile) => mb_strtolower(trim($profile['name'])))
                ->flatMap(fn ($group) => $group->take(3))
                ->values()
                ->toArray()
        );
    }

    public function store(StoreUserConnectionRequest $request): RedirectResponse
    {
        $currentUserId = auth()->id();
        $receiverId = (int) $request->user_id;

        abort_if($receiverId === $currentUserId, 422);

        if ($request->type === 'friend') {
            $existingConnection = UserConnection::query()
                ->where('type', 'friend')
                ->where(function ($query) use ($currentUserId, $receiverId) {
                    $query
                        ->where(function ($query) use ($currentUserId, $receiverId) {
                            $query
                                ->where('sender_id', $currentUserId)
                                ->where('receiver_id', $receiverId);
                        })
                        ->orWhere(function ($query) use ($currentUserId, $receiverId) {
                            $query
                                ->where('sender_id', $receiverId)
                                ->where('receiver_id', $currentUserId);
                        });
                })
                ->exists();

            abort_if($existingConnection, 422);
        }

        UserConnection::updateOrCreate(
            [
                'sender_id' => $currentUserId,
                'receiver_id' => $receiverId,
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

    public function accept(UserConnection $connection): RedirectResponse
    {
        abort_unless($connection->receiver_id === auth()->id(), 403);
        abort_unless($connection->type === 'friend', 404);

        $connection->update([
            'status' => 'accepted',
        ]);

        return back();
    }

    public function destroy(UserConnection $connection): RedirectResponse
    {
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
            'is_curator' => $user->is_curator,
        ];
    }

    private function incomingRequests(): array
    {
        return UserConnection::query()
            ->with('sender')
            ->where('receiver_id', auth()->id())
            ->where('type', 'friend')
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(fn ($connection) => [
                'id' => $connection->id,
                'status' => $connection->status,
                'created_at' => $connection->created_at?->diffForHumans(),
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
            ->with(['sender', 'receiver'])
            ->where(function ($query) {
                $query
                    ->where('sender_id', auth()->id())
                    ->orWhere('receiver_id', auth()->id());
            })
            ->latest()
            ->get()
            ->map(function ($connection) {
                $otherUser = $connection->sender_id === auth()->id()
                    ? $connection->receiver
                    : $connection->sender;

                return [
                    'id' => $connection->id,
                    'type' => $connection->type,
                    'status' => $connection->status,
                    'is_incoming' => $connection->receiver_id === auth()->id(),
                    'created_at' => $connection->created_at?->diffForHumans(),
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