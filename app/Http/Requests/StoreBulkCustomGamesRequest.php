<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkCustomGamesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titles' => ['required', 'array', 'min:1', 'max:100'],
            'titles.*' => ['required', 'string', 'min:2', 'max:255'],
        ];
    }
}
