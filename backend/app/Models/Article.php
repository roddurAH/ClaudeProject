<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'slug', 'category', 'audience', 'icon', 'image_path', 'title_bn', 'title_en',
        'summary_bn', 'summary_en', 'body_bn', 'body_en', 'sort_order', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function imageUrl(): ?string
    {
        return Media::url($this->image_path);
    }
}
