<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomGame extends Model
{
    protected $fillable = [
        'user_id',
        'igdb_id',
        'igdb_slug',
        'igdb_url',
        'nintendo_title_id',
        'title',
        'normalized_title',
        'publisher',
        'developer',
        'description',
        'release_date',
        'cover_url',
        'header_image_url',
        'source',
        'platform',
        'playtime_minutes',
        'achievements_unlocked',
        'achievements_total',
        'first_played_at',
        'last_played_at',
    ];

    protected $casts = [
        'release_date' => 'date',
        'first_played_at' => 'datetime',
        'last_played_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
