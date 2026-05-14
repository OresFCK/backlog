<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGameMeta extends Model
{
    protected $table = 'user_game_meta';

    protected $fillable = [
        'user_id',
        'game_id',
        'note',
        'rating',
        'recommended',
    ];

    protected $casts = [
        'recommended' => 'boolean',
        'rating' => 'integer',
    ];
}