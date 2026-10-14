<?php

namespace App\Http\Requests\Api;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    use NormalizesBanglaInput;

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:100'],
            'district_id' => ['sometimes', 'nullable', 'integer', 'exists:districts,id'],
            'user_type' => ['sometimes', 'nullable', Rule::enum(UserType::class)],
            'preferred_language' => ['sometimes', Rule::in(['bn', 'en'])],
            'current_pin' => ['required_with:pin', 'string'],
            'pin' => ['sometimes', 'string', 'regex:/^\d{4,6}$/'],
        ];
    }
}
