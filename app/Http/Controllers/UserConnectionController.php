<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserConnectionRequest;
use App\Models\User;
use App\Models\UserConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserConnectionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render(
            'people/index',
            [
                'user' => $this->currentUser(),
                'connections' => $this->connections(),
                'incomingRequests' => $this->incomingRequests(),
            ]
        );
    }

    public function notifications(): JsonResponse
    {
        return response()->json([
            'incoming_requests_count' => UserConnection::query()
                ->where('receiver_id', auth()->id())
                ->where('type', 'friend')
                ->where('status', 'pending')
                ->count(),

            'incoming_requests' => $this->incomingRequests(),
        ]);
    }

    public function search(): JsonResponse
    {
        $query = request('q');

        if (! $query) {
            return response()->json([]);
        }

        return response()->json(
            User::query()
                ->where('id', '!=', auth()->id())
                ->where(function ($builder) use ($query) {
                    $builder
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('steam_id', 'like', "%{$query}%");
                })
                ->limit(8)
                ->get()
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'steam_id' => $user->steam_id,
                    'avatar' => $user->steam_avatar_url,
                ])
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

        return [
            'name' => $user->name,
            'avatar' => $user->steam_avatar_url,
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
                    'name' => $connection->sender->name,
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
                        'name' => $otherUser->name,
                        'steam_id' => $otherUser->steam_id,
                        'avatar' => $otherUser->steam_avatar_url,
                    ],
                ];
            })
            ->toArray();
    }
}