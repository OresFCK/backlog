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
        'game_title',
        'title',
        'body',
        'rating',
        'recommended',
        'not_recommended',
    ];

    protected $casts = [
        'recommended' => 'boolean',
        'not_recommended' => 'boolean',
        'rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(
            Game::class
        );
    }

    public function votes(): HasMany
    {
        return $this->hasMany(
            PublicReviewVote::class
        );
    }
}