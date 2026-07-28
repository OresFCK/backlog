<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPostReport extends Model
{
    protected $fillable = [
        'blog_post_id',
        'reporter_id',
        'reason',
        'status',
    ];
}
