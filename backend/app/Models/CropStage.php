<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CropStage extends Model
{
    protected $fillable = [
        'crop_id', 'day_offset', 'stage_type', 'title_bn', 'title_en', 'details_bn', 'details_en',
    ];

    protected $touches = ['crop']; // editing a stage changes the content version

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }
}
