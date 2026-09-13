<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NintendoAccount extends Model
{
    protected $fillable = ['user_id', 'session_token', 'last_synced_at'];

    protected $hidden = ['session_token'];

    protected function casts(): array
    {
        return [
            'session_token' => 'encrypted',
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
