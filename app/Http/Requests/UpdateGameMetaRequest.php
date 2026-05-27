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
            
            'not_recommended' => ['
                boolean'
            ],

            'status' => [
                'required',
            ],
        ];
    }
}