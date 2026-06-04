<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class GameTitleNormalizer
{
    public static function normalize(string $title): string
    {
        return Str::of($title)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->replaceMatches('/\b(the|game|edition|standard|deluxe|ultimate|complete)\b/', '')
            ->squish()
            ->toString();
    }
}