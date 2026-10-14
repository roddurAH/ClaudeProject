<?php

namespace App\Http\Requests;

use App\Http\Requests\Api\NormalizesBanglaInput;
use Illuminate\Foundation\Http\FormRequest;

/** Public account-deletion form (/account/delete): phone + PIN + confirmation. */
class DeleteAccountWebRequest extends FormRequest
{
    use NormalizesBanglaInput;

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'pin' => ['required', 'string', 'max:64'],
            'confirm' => ['accepted'],
        ];
    }
}
