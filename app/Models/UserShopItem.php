<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserShopItem extends Model
{
    protected $fillable = [
        'user_id',
        'shop_item_id',
        'is_equipped',
        'is_featured_on_profile',
    ];

    protected $casts = [
        'is_equipped' => 'boolean',
        'is_featured_on_profile' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(
            ShopItem::class,
            'shop_item_id'
        );
    }
}