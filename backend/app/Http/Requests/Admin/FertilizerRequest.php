<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FertilizerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name_bn' => ['required', 'string', 'max:80'],
            'name_en' => ['required', 'string', 'max:80'],
            'official_price_per_kg' => ['nullable', 'numeric', 'between:0,10000'],
            'notes_bn' => ['nullable', 'string', 'max:2000'],
            'notes_en' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'between:0,65000'],
        ];
    }
}
