<?php

namespace App\Http\Requests\Admin;

use App\Enums\Options;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommodityRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'crop_id' => ['nullable', 'integer', 'exists:crops,id'],
            'name_bn' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(Options::keys(Options::CROP_CATEGORIES))],
            'unit' => ['required', Rule::in(Options::keys(Options::UNITS))],
            'icon' => ['nullable', 'string', 'max:16'],
            'sort_order' => ['nullable', 'integer', 'between:0,65000'],
            'is_active' => ['boolean'],
        ];
    }
}
