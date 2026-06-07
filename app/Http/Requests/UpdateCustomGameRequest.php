<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customGame = $this->route('customGame');

        return $customGame && $customGame->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'developer' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'release_date' => ['nullable', 'date'],
            'cover_url' => ['nullable', 'string', 'max:2000'],
            'header_image_url' => ['nullable', 'string', 'max:2000'],
            'igdb_url' => ['nullable', 'string', 'max:2000'],
            'platform' => ['nullable', 'string', 'max:255'],

            'playtime_hours' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'achievements_unlocked' => [
                'nullable',
                'integer',
                'min:0',
                'max:100000',
                'lte:achievements_total',
            ],
            'achievements_total' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'achievements_unlocked.lte' => 'Unlocked achievements cannot be greater than total achievements.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        if (array_key_exists('playtime_hours', $validated)) {
            $validated['playtime_minutes'] = filled($validated['playtime_hours'])
                ? (int) round(((float) $validated['playtime_hours']) * 60)
                : null;

            unset($validated['playtime_hours']);
        }

        return $validated;
    }
}