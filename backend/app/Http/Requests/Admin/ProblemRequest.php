<?php

namespace App\Http\Requests\Admin;

use App\Enums\Options;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProblemRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['is_published' => $this->boolean('is_published')]);
    }

    public function rules(): array
    {
        $text = ['nullable', 'string', 'max:5000'];

        return [
            'crop_id' => ['required', 'integer', 'exists:crops,id'],
            'type' => ['required', Rule::in(Options::keys(Options::PROBLEM_TYPES))],
            'affected_part' => ['required', Rule::in(Options::keys(Options::AFFECTED_PARTS))],
            'severity' => ['required', Rule::in(Options::keys(Options::SEVERITIES))],
            'name_bn' => ['required', 'string', 'max:150'],
            'name_en' => ['required', 'string', 'max:150'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('krishi.uploads.max_kb')],
            'symptoms_bn' => ['required', 'string', 'max:5000'],
            'symptoms_en' => ['required', 'string', 'max:5000'],
            'prevention_bn' => $text,
            'prevention_en' => $text,
            'organic_control_bn' => $text,
            'organic_control_en' => $text,
            'chemical_control_bn' => $text,
            'chemical_control_en' => $text,
            'is_published' => ['boolean'],
        ];
    }
}
