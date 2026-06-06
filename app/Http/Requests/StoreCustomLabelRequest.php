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
                'max:18',
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