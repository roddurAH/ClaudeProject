<?php

namespace App\Http\Requests\Api;

use App\Enums\UserType;
use App\Support\BanglaText;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    use NormalizesBanglaInput;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'regex:'.BanglaText::PHONE_REGEX, 'unique:users,phone'],
            'pin' => ['required', 'string', 'regex:/^\d{4,6}$/'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'user_type' => ['nullable', Rule::enum(UserType::class)],
            'preferred_language' => ['nullable', Rule::in(['bn', 'en'])],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => __('Enter a valid Bangladeshi mobile number (01XXXXXXXXX).'),
            'phone.unique' => __('This mobile number is already registered. Please log in.'),
            'pin.regex' => __('PIN must be 4 to 6 digits.'),
        ];
    }
}
