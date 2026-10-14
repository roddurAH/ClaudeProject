<?php

namespace App\Http\Resources;

use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Crop */
class CropResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name_bn' => $this->name_bn,
            'name_en' => $this->name_en,
            'category' => $this->category,
            'icon' => $this->icon,
            'image_url' => $this->imageUrl(),
            'season' => $this->season,
            'sowing_months' => $this->sowing_months ?? [],
            'harvest_months' => $this->harvest_months ?? [],
            'duration_days' => $this->duration_days,
            'is_garden_friendly' => $this->is_garden_friendly,
            'summary_bn' => $this->summary_bn,
            'summary_en' => $this->summary_en,
            // Long guide text only in detail responses (where stages are loaded).
            'cultivation_bn' => $this->when($this->relationLoaded('stages'), $this->cultivation_bn),
            'cultivation_en' => $this->when($this->relationLoaded('stages'), $this->cultivation_en),
            'stages' => CropStageResource::collection($this->whenLoaded('stages')),
            'problems' => ProblemResource::collection($this->whenLoaded('problems')),
            'fertilizer_doses' => FertilizerDoseResource::collection($this->whenLoaded('fertilizerDoses')),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
