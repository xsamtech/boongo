<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DirectPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'transaction_type_id' => ['required', 'integer'],
            'other_phone' => ['nullable', 'string', 'regex:/^\+?[0-9]{7,15}$/'],
            'channel' => ['nullable', 'string', 'max:45'],
            'app_url' => ['nullable', 'url:http,https', 'max:2048'],
        ];
    }
}
