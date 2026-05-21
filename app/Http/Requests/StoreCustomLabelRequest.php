<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCustomLabelRequest extends FormRequest
{
    private const BLOCKED_WORDS = [
        'hitler',
        'nazi',
        '1488',
        'heilhitler',
        'nigger',
        'nigga',
        'nigg',
        'kike',
        'chink',
        'spic',
        'faggot',
        'tranny',
        'isis',
        'alqaeda',
        'pedo',
        'pedophile',
    ];

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
                'min:2',
                'max:24',
            ],

            'color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Label title is required.',
            'title.min' => 'Label title must be at least 2 characters.',
            'title.max' => 'Label title cannot be longer than 24 characters.',
            'color.required' => 'Label color is required.',
            'color.regex' => 'Label color must be a valid hex color.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->containsBlockedWord()) {
                $validator
                    ->errors()
                    ->add(
                        'title',
                        'This label title is not allowed.'
                    );
            }
        });
    }

    private function containsBlockedWord(): bool
    {
        $title = $this->normalizedTitle();

        foreach (self::BLOCKED_WORDS as $word) {
            if (str_contains($title, $word)) {
                return true;
            }
        }

        return false;
    }

    private function normalizedTitle(): string
    {
        $title = strtolower(
            $this->input('title', '')
        );

        return preg_replace(
            '/[^a-z0-9]/',
            '',
            $title
        );
    }
}