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
            'title' => ['required', 'string', 'max:255'],
            'igdb_id' => ['nullable', 'integer'],
            'igdb_slug' => ['nullable', 'string', 'max:255'],
            'igdb_url' => ['nullable', 'string', 'max:2000'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'developer' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'release_date' => ['nullable', 'date'],
            'cover_url' => ['nullable', 'string', 'max:2000'],
            'header_image_url' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', 'in:manual,igdb,steam'],
            'platform' => ['nullable', 'string', 'max:255'],
        ];
    }
}