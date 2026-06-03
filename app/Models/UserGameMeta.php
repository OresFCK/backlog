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
        'not_recommended',
        'status',
        'show_on_public_profile',
    ];

    protected $casts = [
        'recommended' => 'boolean',
        'not_recommended' => 'boolean',
        'show_on_public_profile' => 'boolean',
        'rating' => 'integer',
    ];
}