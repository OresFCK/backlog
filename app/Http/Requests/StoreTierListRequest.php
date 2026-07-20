<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTierListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'tiers' => ['required', 'array', 'min:2', 'max:12'],
            'tiers.*.id' => ['required', 'string', 'max:40', 'distinct'],
            'tiers.*.name' => ['required', 'string', 'max:40'],
            'tiers.*.color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.game_id' => ['required', 'integer', 'distinct', 'exists:games,id'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.slug' => ['required', 'string', 'max:255'],
            'items.*.image_url' => ['nullable', 'string', 'max:2048'],
            'items.*.tier_id' => ['nullable', 'string', 'max:40'],
            'items.*.position' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }
}
