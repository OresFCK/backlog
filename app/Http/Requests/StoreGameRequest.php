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
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'steam_app_id' => [
                'nullable',
                'integer',
            ],

            'cover_url' => [
                'nullable',
                'url',
            ],

            'header_image_url' => [
                'nullable',
                'url',
            ],

            'release_date' => [
                'nullable',
                'date',
            ],

            'metacritic_score' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],

            'steam_rating_percent' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],

            'average_playtime_minutes' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }
}