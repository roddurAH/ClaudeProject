<?php

namespace App\Http\Requests\Admin;

use App\Enums\Options;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CropStageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'day_offset' => ['required', 'integer', 'between:-120,1000'],
            'stage_type' => ['required', Rule::in(Options::keys(Options::STAGE_TYPES))],
            'title_bn' => ['required', 'string', 'max:150'],
            'title_en' => ['required', 'string', 'max:150'],
            'details_bn' => ['nullable', 'string', 'max:3000'],
            'details_en' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
