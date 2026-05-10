<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Game extends Model
{
    protected $fillable = [
        'steam_app_id',
        'title',
        'cover_url',
        'header_image_url',
        'release_date',
        'metacritic_score',
        'steam_rating_percent',
        'average_playtime_minutes',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function userGames(): HasMany
    {
        return $this->hasMany(UserGame::class);
    }
}