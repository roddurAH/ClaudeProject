<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    use NormalizesBanglaInput;

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'pin' => ['required', 'string', 'max:64'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
