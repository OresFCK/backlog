<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomListItem extends Model
{
    protected $fillable = [
        'custom_list_id',
        'game_id',
        'game_title',
        'game_cover_url',
        'game_slug',
        'steam_app_id',
        'position',
        'note',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(
            CustomList::class,
            'custom_list_id'
        );
    }
}