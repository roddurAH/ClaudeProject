<?php

namespace App\Http\Requests\Admin;

use App\Enums\AlertSeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlertRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['is_national' => $this->boolean('is_national')]);
    }

    public function rules(): array
    {
        return [
            'severity' => ['required', Rule::enum(AlertSeverity::class)],
            'title_bn' => ['required', 'string', 'max:200'],
            'title_en' => ['required', 'string', 'max:200'],
            'body_bn' => ['required', 'string', 'max:3000'],
            'body_en' => ['required', 'string', 'max:3000'],
            'is_national' => ['boolean'],
            'district_ids' => ['exclude_if:is_national,true', 'required', 'array', 'min:1'],
            'district_ids.*' => ['integer', 'exists:districts,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ];
    }

    public function messages(): array
    {
        return ['district_ids.required' => 'Choose at least one district, or mark the alert as national.'];
    }
}
