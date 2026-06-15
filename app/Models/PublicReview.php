<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublicReview extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'source',
        'source_game_id',
        'game_title',
        'title',
        'body',
        'rating',
        'platform',
        'screenshot_path',
        'recommended',
        'not_recommended',
        'is_featured_on_profile',
        'is_public',
        'time_to_beat_minutes',
    ];

    protected $casts = [
        'recommended' => 'boolean',
        'not_recommended' => 'boolean',
        'is_featured_on_profile' => 'boolean',
        'is_public' => 'boolean',
        'rating' => 'integer',
        'time_to_beat_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PublicReviewVote::class);
    }
}