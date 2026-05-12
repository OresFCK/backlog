<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomGame extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'publisher',
        'cover_url',
    ];
}