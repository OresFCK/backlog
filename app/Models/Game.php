<?php

namespace App\Models;

use App\Helpers\GameTitleNormalizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Game extends Model
{
    protected $fillable = [
        'steam_app_id',
        'igdb_id',
        'title',
        'normalized_title',
        'slug',
        'summary',
        'source',
        'igdb_cover_id',
        'igdb_cover_image_id',
        'igdb_cover_url',
        'cover_url',
        'header_image_url',
        'release_date',
        'metacritic_score',
        'steam_rating_percent',
        'average_playtime_minutes',
    ];

    protected $casts = [
        'release_date' => 'date',
        'igdb_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Game $game) {
            if ($game->title && ! $game->normalized_title) {
                $game->normalized_title = GameTitleNormalizer::normalize($game->title);
            }

            if ($game->title && ! $game->slug) {
                $baseSlug = Str::slug($game->title);

                if (blank($baseSlug)) {
                    $baseSlug = 'game-' . ($game->id ?: uniqid());
                }

                $slug = $baseSlug;
                $counter = 2;

                while (
                    static::query()
                        ->where('slug', $slug)
                        ->when($game->exists, fn ($query) => $query->whereKeyNot($game->id))
                        ->exists()
                ) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }

                $game->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function userGames(): HasMany
    {
        return $this->hasMany(UserGame::class);
    }

    public function publicReviews(): HasMany
    {
        return $this->hasMany(PublicReview::class);
    }
}