<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Problem extends Model
{
    use HasFactory;

    protected $fillable = [
        'crop_id', 'type', 'affected_part', 'name_bn', 'name_en', 'severity', 'image_path',
        'symptoms_bn', 'symptoms_en', 'prevention_bn', 'prevention_en',
        'organic_control_bn', 'organic_control_en', 'chemical_control_bn', 'chemical_control_en',
        'is_published',
    ];

    protected $touches = ['crop']; // editing a problem changes the content version

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function imageUrl(): ?string
    {
        return Media::url($this->image_path);
    }
}
