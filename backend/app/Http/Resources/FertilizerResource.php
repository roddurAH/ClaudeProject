<?php

namespace App\Http\Resources;

use App\Models\Fertilizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Fertilizer */
class FertilizerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name_bn' => $this->name_bn,
            'name_en' => $this->name_en,
            'official_price_per_kg' => $this->official_price_per_kg,
            'notes_bn' => $this->notes_bn,
            'notes_en' => $this->notes_en,
        ];
    }
}
