<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $fillable = ['division_id', 'name_en', 'name_bn', 'latitude', 'longitude'];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function markets(): HasMany
    {
        return $this->hasMany(Market::class);
    }

    /** Great-circle distance in km (haversine). */
    public function distanceTo(District $other): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($other->latitude - $this->latitude);
        $dLon = deg2rad($other->longitude - $this->longitude);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($this->latitude)) * cos(deg2rad($other->latitude)) * sin($dLon / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
