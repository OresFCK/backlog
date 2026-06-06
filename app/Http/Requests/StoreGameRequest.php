<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],

            'steam_app_id' => ['nullable', 'integer'],
            'igdb_id' => ['nullable', 'integer'],

            'slug' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'max:255'],

            'igdb_cover_id' => ['nullable', 'integer'],
            'igdb_cover_image_id' => ['nullable', 'string', 'max:255'],
            'igdb_cover_url' => ['nullable', 'url', 'max:2000'],

            'cover_url' => ['nullable', 'url', 'max:2000'],
            'header_image_url' => ['nullable', 'url', 'max:2000'],

            'release_date' => ['nullable', 'date'],

            'metacritic_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'steam_rating_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'average_playtime_minutes' => ['nullable', 'integer', 'min:0'],

            'playtime_minutes' => ['nullable', 'integer', 'min:0'],
            'achievements_unlocked' => ['nullable', 'integer', 'min:0'],
            'achievements_total' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $unlocked = $this->input('achievements_unlocked');
            $total = $this->input('achievements_total');

            if (
                filled($unlocked) &&
                filled($total) &&
                (int) $unlocked > (int) $total
            ) {
                $validator
                    ->errors()
                    ->add('achievements_unlocked', 'Unlocked achievements cannot be greater than total achievements.');
            }
        });
    }
}