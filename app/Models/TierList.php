<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TierList extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'description',
        'tiers',
        'items',
        'is_public',
    ];

    protected $casts = [
        'tiers' => 'array',
        'items' => 'array',
        'is_public' => 'boolean',
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
