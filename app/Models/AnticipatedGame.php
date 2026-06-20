<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnticipatedGame extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
    ];
}