<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CropFertilizerDose extends Model
{
    protected $fillable = ['crop_id', 'fertilizer_id', 'kg_per_hectare', 'application_bn', 'application_en'];

    protected $touches = ['crop'];

    protected function casts(): array
    {
        return ['kg_per_hectare' => 'float'];
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function fertilizer(): BelongsTo
    {
        return $this->belongsTo(Fertilizer::class);
    }
}
