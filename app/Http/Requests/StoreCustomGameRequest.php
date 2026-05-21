<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomGameRequest extends FormRequest
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

            'publisher' => [
                'nullable',
                'string',
                'max:255',
            ],

            'cover_url' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}