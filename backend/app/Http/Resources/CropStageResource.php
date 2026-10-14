<?php

namespace App\Http\Resources;

use App\Models\CropStage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CropStage */
class CropStageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day_offset' => $this->day_offset,
            'stage_type' => $this->stage_type,
            'title_bn' => $this->title_bn,
            'title_en' => $this->title_en,
            'details_bn' => $this->details_bn,
            'details_en' => $this->details_en,
        ];
    }
}
