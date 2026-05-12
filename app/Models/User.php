<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'steam_id',
        'steam_persona_name',
        'steam_avatar_url',
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
        ];
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
}