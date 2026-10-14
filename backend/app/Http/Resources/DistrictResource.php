<?php

namespace App\Http\Resources;

use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin District */
class DistrictResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'division_id' => $this->division_id,
            'division_bn' => $this->whenLoaded('division', fn () => $this->division->name_bn),
            'division_en' => $this->whenLoaded('division', fn () => $this->division->name_en),
            'name_bn' => $this->name_bn,
            'name_en' => $this->name_en,
            'lat' => $this->latitude,
            'lon' => $this->longitude,
        ];
    }
}
