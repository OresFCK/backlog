<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CustomStatusSeeder extends Seeder
{
    public function run(): void
    {
        User::all()->each(function ($user) {

            if (
                $user
                    ->customStatuses()
                    ->exists()
            ) {
                return;
            }

            $user
                ->customStatuses()
                ->createMany([
                    [
                        'name' => 'Backlog',

                        'color' => '#71717a',
                    ],

                    [
                        'name' => 'Playing',

                        'color' => '#3b82f6',
                    ],

                    [
                        'name' => 'Finished',

                        'color' => '#22c55e',
                    ],

                    [
                        'name' => 'Planned',

                        'color' => '#a855f7',
                    ],

                    [
                        'name' => 'Dropped',

                        'color' => '#ef4444',
                    ],
                ]);
        });
    }
}