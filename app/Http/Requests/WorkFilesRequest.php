<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class WorkFilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'files_urls' => ['sometimes', 'array'],
            'files_urls.*' => ['file'],
            'part_covers' => ['sometimes', 'array'],
            'part_covers.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,bmp,gif,webp'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $covers = $this->file('part_covers', []);
            if (! is_array($covers)) {
                return;
            }

            foreach ($covers as $index => $cover) {
                if ($cover !== null && ! $this->hasFile('files_urls.'.$index)) {
                    $validator->errors()->add(
                        'part_covers.'.$index,
                        'La couverture doit accompagner un fichier files_urls au même index.'
                    );
                }
            }
        });
    }
}
