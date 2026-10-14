<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketPrice extends Model
{
    protected $fillable = [
        'commodity_id', 'market_id', 'price_date', 'price_type', 'min_price', 'max_price', 'source', 'entered_by',
    ];

    protected function casts(): array
    {
        return [
            'price_date' => 'date',
            'min_price' => 'float',
            'max_price' => 'float',
        ];
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function average(): float
    {
        return round(($this->min_price + $this->max_price) / 2, 2);
    }
}
