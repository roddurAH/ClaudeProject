<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fertilizer extends Model
{
    protected $fillable = [
        'key', 'name_bn', 'name_en', 'official_price_per_kg', 'notes_bn', 'notes_en', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['official_price_per_kg' => 'float'];
    }
}
