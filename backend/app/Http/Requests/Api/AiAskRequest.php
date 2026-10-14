<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** AI doctor: a photo, a question, or both. */
class AiAskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'image' => ['nullable', 'required_without:text', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('krishi.uploads.max_kb')],
            'text' => ['nullable', 'required_without:image', 'string', 'max:1000'],
            'crop_id' => ['nullable', 'integer', Rule::exists('crops', 'id')->where('is_published', true)],
            'conversation_id' => ['nullable', 'integer'],
        ];
    }
}
