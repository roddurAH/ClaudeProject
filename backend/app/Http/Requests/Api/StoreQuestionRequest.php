<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:5', 'max:2000'],
            'crop_id' => ['nullable', 'integer', Rule::exists('crops', 'id')->where('is_published', true)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('krishi.uploads.max_kb')],
        ];
    }
}
