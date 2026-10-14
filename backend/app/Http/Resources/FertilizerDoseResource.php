<?php

namespace App\Http\Resources;

use App\Models\CropFertilizerDose;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CropFertilizerDose */
class FertilizerDoseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'fertilizer_id' => $this->fertilizer_id,
            'fertilizer_key' => $this->whenLoaded('fertilizer', fn () => $this->fertilizer->key),
            'kg_per_hectare' => $this->kg_per_hectare,
            'application_bn' => $this->application_bn,
            'application_en' => $this->application_en,
        ];
    }
}
