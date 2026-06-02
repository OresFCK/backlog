<?php

namespace App\Helpers;

class LevelSystem
{
    public static function levelFromXp(
        int $xp
    ): int {

        return (int) floor(
            sqrt($xp / 100)
        ) + 1;
    }

    public static function xpForNextLevel(
        int $level
    ): int {

        return ($level ** 2) * 100;
    }
}