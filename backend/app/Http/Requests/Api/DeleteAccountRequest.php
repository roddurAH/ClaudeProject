<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/** The app asks for the PIN again before deleting an account. */
class DeleteAccountRequest extends FormRequest
{
    use NormalizesBanglaInput;

    public function rules(): array
    {
        return [
            'pin' => ['required', 'string', 'max:64'],
        ];
    }
}
