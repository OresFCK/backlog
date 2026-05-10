<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'user_game_id',
        'score',
        'reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userGame(): BelongsTo
    {
        return $this->belongsTo(UserGame::class);
    }
}