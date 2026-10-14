<?php

namespace App\Http\Resources;

use App\Models\Market;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Market */
class MarketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_bn' => $this->name_bn,
            'name_en' => $this->name_en,
            'type' => $this->type,
            'district_id' => $this->district_id,
            'district_bn' => $this->whenLoaded('district', fn () => $this->district->name_bn),
            'district_en' => $this->whenLoaded('district', fn () => $this->district->name_en),
        ];
    }
}
