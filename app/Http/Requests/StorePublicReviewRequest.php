<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicReviewRequest extends FormRequest
{
    public const PLATFORMS = [
        'pc',
        'steam_deck',
        'playstation_5',
        'playstation_4',
        'xbox_series',
        'xbox_one',
        'nintendo_switch',
        'nintendo_switch_2',
        'ios',
        'android',
        'other',
    ];

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'source' => ['nullable', 'string', 'max:50'],
            'source_game_id' => ['nullable', 'string', 'max:255'],

            'game_title' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'rating' => ['required', 'integer', 'min:1', 'max:10'],

            'platform' => [
                'nullable',
                'string',
                'in:' . implode(',', self::PLATFORMS),
            ],

            'screenshot' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'recommended' => ['boolean'],
            'not_recommended' => ['boolean'],
            'is_featured_on_profile' => ['boolean'],
            'time_to_beat_hours' => ['nullable', 'numeric', 'min:0', 'max:9999'],
        ];
    }
}