<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use App\Enums\UserType;
use App\Support\BanglaText;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => BanglaText::normalizePhone((string) $this->input('phone')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $role = Role::tryFrom((string) $this->input('role'));
        // Staff need a real password; app users keep a 4–6 digit PIN.
        $passwordRule = $role && $role->isStaff()
            ? Password::min(8)->letters()->numbers()
            : 'regex:/^\d{4,6}$/';

        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'regex:'.BanglaText::PHONE_REGEX, Rule::unique('users', 'phone')->ignore($user)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::enum(Role::class)],
            'user_type' => ['nullable', Rule::enum(UserType::class)],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'password' => [$user ? 'nullable' : 'required', 'string', $passwordRule],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return ['password.regex' => 'App users need a 4–6 digit PIN as password.'];
    }
}
