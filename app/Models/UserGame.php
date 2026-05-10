<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGame extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'platform_id',
        'status',
        'priority',
        'source',
        'playtime_minutes',
        'personal_rating',
        'notes',
        'added_to_library_at',
        'started_at',
        'finished_at',
        'last_played_at',
    ];

    protected $casts = [
        'added_to_library_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'last_played_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}