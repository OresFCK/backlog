<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserShopItem extends Model
{
    protected $fillable = [
        'user_id',
        'shop_item_id',
        'is_equipped',
    ];

    protected $casts = [
        'is_equipped' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(ShopItem::class, 'shop_item_id');
    }
}