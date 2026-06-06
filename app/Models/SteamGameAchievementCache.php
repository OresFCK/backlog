<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SteamGameAchievementCache extends Model
{
    protected $table = 'steam_game_achievements_cache';

    protected $fillable = [
        'user_id',
        'steam_id',
        'steam_app_id',
        'unlocked',
        'total',
        'percent',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];
}