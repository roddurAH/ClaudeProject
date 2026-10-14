<?php

namespace App\Http\Requests\Admin;

use App\Enums\Options;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CropRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('name_en')),
            'is_garden_friendly' => $this->boolean('is_garden_friendly'),
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    public function rules(): array
    {
        $crop = $this->route('crop');

        return [
            'slug' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('crops', 'slug')->ignore($crop)],
            'name_bn' => ['required', 'string', 'max:80'],
            'name_en' => ['required', 'string', 'max:80'],
            'category' => ['required', Rule::in(Options::keys(Options::CROP_CATEGORIES))],
            'icon' => ['nullable', 'string', 'max:16'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('krishi.uploads.max_kb')],
            'season' => ['nullable', Rule::in(Options::keys(Options::SEASONS))],
            'sowing_months' => ['nullable', 'array'],
            'sowing_months.*' => ['integer', 'between:1,12'],
            'harvest_months' => ['nullable', 'array'],
            'harvest_months.*' => ['integer', 'between:1,12'],
            'duration_days' => ['nullable', 'integer', 'between:1,2000'],
            'is_garden_friendly' => ['boolean'],
            'summary_bn' => ['nullable', 'string', 'max:1000'],
            'summary_en' => ['nullable', 'string', 'max:1000'],
            'cultivation_bn' => ['nullable', 'string', 'max:20000'],
            'cultivation_en' => ['nullable', 'string', 'max:20000'],
            'sort_order' => ['nullable', 'integer', 'between:0,65000'],
            'is_published' => ['boolean'],
        ];
    }
}
