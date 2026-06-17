<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'display_name',
        'email',
        'password',
        'steam_id',
        'steam_persona_name',
        'steam_avatar_url',
        'banner_url',
        'xp',
        'level',
        'profile_level_multiplier_enabled',
        'xp_multiplier',
        'is_admin',
        'coins',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'coins' => 'integer',
        ];
    }

    public function getVisibleNameAttribute(): string
    {
        return $this->display_name
            ?: $this->steam_persona_name
            ?: $this->name;
    }

    public function steamAccount(): HasOne
    {
        return $this->hasOne(SteamAccount::class);
    }

    public function userGames(): HasMany
    {
        return $this->hasMany(UserGame::class);
    }

    public function gameRecommendations(): HasMany
    {
        return $this->hasMany(GameRecommendation::class);
    }

    public function customGames(): HasMany
    {
        return $this->hasMany(CustomGame::class);
    }

    public function customStatuses()
    {
        return $this->hasMany(CustomStatus::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(UserSubmission::class);
    }

    public function challenges(): BelongsToMany
    {
        return $this->belongsToMany(Challenge::class)
            ->withPivot('completed_at')
            ->withTimestamps();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
    
    public function steamGames()
    {
        return $this->hasMany(\App\Models\UserSteamGame::class);
    }
}