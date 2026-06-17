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
        'last_synced_at',
    ];
}