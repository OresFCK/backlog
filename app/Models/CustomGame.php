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
        'title',
        'normalized_title',
        'publisher',
        'cover_url',
        'source',
    ];

    protected $casts = [
        'igdb_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (CustomGame $game) {
            if ($game->title) {
                $game->normalized_title = GameTitleNormalizer::normalize($game->title);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}