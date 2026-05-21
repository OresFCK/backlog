<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomLabelRequest extends FormRequest
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
                'min:2',
                'max:24',
            ],

            'color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $blocked = [
                'hitler',
                'nigger',
                'nigga',
            ];

            $title = str(
                $this->title
            )->lower();

            foreach ($blocked as $word) {

                if (
                    $title->contains($word)
                ) {

                    $validator
                        ->errors()
                        ->add(
                            'title',
                            'Blocked word detected.'
                        );

                    break;
                }
            }
        });
    }
}