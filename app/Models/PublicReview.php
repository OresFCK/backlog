<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicReview extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'title',
        'body',
        'rating',
        'recommended',
        'is_public',
    ];

    protected $casts = [
        'rating' => 'integer',
        'recommended' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}