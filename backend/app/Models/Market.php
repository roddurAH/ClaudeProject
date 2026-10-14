<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Market extends Model
{
    protected $fillable = ['district_id', 'name_bn', 'name_en', 'type', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(MarketPrice::class);
    }
}
