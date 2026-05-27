<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'game_id' => [
                'required',
                'string',
                'max:255',
            ],
            'game_title' => [
                'required',
                'string',
            ],
            
            'title' => [
                'nullable',
                'string',
                'max:120',
            ],

            'body' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],

            'rating' => [
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],

            'recommended' => [
                'boolean',
            ],
            'not_recommended' => [
                'boolean',
            ],
        ];
    }
}