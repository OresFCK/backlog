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
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}