<?php

namespace App\Http\Requests\Admin;

use App\Enums\Options;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarketRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'name_bn' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(Options::keys(Options::MARKET_TYPES))],
            'is_active' => ['boolean'],
        ];
    }
}
