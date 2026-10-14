<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Crop extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name_bn', 'name_en', 'category', 'icon', 'image_path', 'season',
        'sowing_months', 'harvest_months', 'duration_days', 'is_garden_friendly',
        'summary_bn', 'summary_en', 'cultivation_bn', 'cultivation_en',
        'sort_order', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'sowing_months' => 'array',
            'harvest_months' => 'array',
            'is_garden_friendly' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(CropStage::class)->orderBy('day_offset');
    }

    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class);
    }

    public function fertilizerDoses(): HasMany
    {
        return $this->hasMany(CropFertilizerDose::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function imageUrl(): ?string
    {
        return Media::url($this->image_path);
    }
}
