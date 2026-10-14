<?php

namespace App\Http\Requests\Admin;

use App\Enums\Options;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('title_en')),
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('articles', 'slug')->ignore($this->route('article'))],
            'category' => ['required', Rule::in(Options::keys(Options::ARTICLE_CATEGORIES))],
            'audience' => ['required', Rule::in(Options::keys(Options::AUDIENCES))],
            'icon' => ['nullable', 'string', 'max:16'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('krishi.uploads.max_kb')],
            'title_bn' => ['required', 'string', 'max:200'],
            'title_en' => ['required', 'string', 'max:200'],
            'summary_bn' => ['nullable', 'string', 'max:1000'],
            'summary_en' => ['nullable', 'string', 'max:1000'],
            'body_bn' => ['required', 'string', 'max:30000'],
            'body_en' => ['required', 'string', 'max:30000'],
            'sort_order' => ['nullable', 'integer', 'between:0,65000'],
            'is_published' => ['boolean'],
        ];
    }
}
