<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicReviewReport extends Model
{
    protected $fillable = [
        'public_review_id',
        'reporter_id',
        'reason',
        'status',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(
            PublicReview::class,
            'public_review_id'
        );
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reporter_id'
        );
    }
}