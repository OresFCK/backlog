<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGameMetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => [
                'nullable',
                'string',
                'max:255'
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

            'show_on_public_profile' => [
                'boolean',
            ],

            'status' => [
                'required',
            ],
        ];
    }
}