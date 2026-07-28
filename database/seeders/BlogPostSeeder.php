<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->oldest('id')->first();

        if (! $author) {
            $this->command?->warn(
                'BlogPostSeeder skipped: create a user first.'
            );

            return;
        }

        BlogPost::query()->updateOrCreate(
            ['slug' => 'how-i-finally-started-finishing-my-backlog'],
            [
                'user_id' => $author->id,
                'title' => 'How I finally started finishing my backlog',
                'excerpt' => 'A simple way to stop scrolling through hundreds of games and actually choose something to play.',
                'body' => <<<'TEXT'
I used to spend more time choosing a game than playing one.

My library kept growing, every sale added another “I will play this later” title, and the backlog slowly became impossible to understand. The solution was not another complicated spreadsheet. I only needed three rules.

First, I separated games I genuinely wanted to play from games I owned by accident. A large library is not the same thing as a useful backlog.

Second, I kept only one long game and one short game in my active rotation. This removed most of the decision fatigue without forcing me to play the same thing every evening.

Finally, I wrote a short note after finishing or dropping a game. Looking back at those notes made future choices much easier and helped me understand what I actually enjoy.

The backlog is still there, but it no longer feels like homework. It is a menu again.
TEXT,
                'youtube_url' => 'https://www.youtube.com/watch?v=5qap5aO4i9A',
                'is_published' => true,
                'published_at' => now(),
            ]
        );
    }
}
