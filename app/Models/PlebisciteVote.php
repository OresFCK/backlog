<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlebisciteVote extends Model
{
    protected $fillable = ['user_id', 'category_key', 'candidate_key'];
}
