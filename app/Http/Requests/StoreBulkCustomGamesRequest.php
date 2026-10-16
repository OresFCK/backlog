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
            'games' => ['required', 'array', 'min:1', 'max:100'],
            'games.*.title' => ['required', 'string', 'min:2', 'max:255'],
            'games.*.igdb_id' => ['nullable', 'integer'],
            'games.*.igdb_slug' => ['nullable', 'string', 'max:255'],
            'games.*.igdb_url' => ['nullable', 'url', 'max:2000'],
            'games.*.publisher' => ['nullable', 'string', 'max:255'],
            'games.*.developer' => ['nullable', 'string', 'max:255'],
            'games.*.description' => ['nullable', 'string'],
            'games.*.release_date' => ['nullable', 'date'],
            'games.*.cover_url' => ['nullable', 'url', 'max:2000'],
            'games.*.header_image_url' => ['nullable', 'url', 'max:2000'],
            'games.*.source' => ['required', 'in:steam,igdb'],
        ];
    }
}
