<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CustomList extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'visibility',
    ];

    protected static function booted(): void
    {
        static::creating(function (CustomList $list) {
            if (! $list->slug) {
                $list->slug = Str::slug($list->title);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomListItem::class)
            ->orderBy('position');
    }
}