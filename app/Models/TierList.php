<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TierList extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'is_public',
        'data',
        'published_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'data' => 'array',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
