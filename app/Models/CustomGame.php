<?php

namespace App\Models;

use App\Helpers\GameTitleNormalizer;
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
        'playtime_minutes',
        'platform',
    ];

    protected $casts = [
        'igdb_id' => 'integer',
        'release_date' => 'date',
        'playtime_minutes' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (CustomGame $game) {
            if ($game->title) {
                $game->normalized_title = GameTitleNormalizer::normalize($game->title);
            }

            if ($game->igdb_slug && ! $game->igdb_url) {
                $game->igdb_url = 'https://www.igdb.com/games/' . $game->igdb_slug;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}