<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSteamGame extends Model
{
    protected $fillable = [
        'user_id',
        'steam_app_id',
        'name',
        'playtime_forever',
        'last_played_at',
        'last_synced_at',
    ];

    protected $casts = [
        'last_played_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];
}
