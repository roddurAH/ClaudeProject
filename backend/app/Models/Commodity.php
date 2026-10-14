<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commodity extends Model
{
    protected $fillable = ['crop_id', 'name_bn', 'name_en', 'category', 'unit', 'icon', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(MarketPrice::class);
    }
}
