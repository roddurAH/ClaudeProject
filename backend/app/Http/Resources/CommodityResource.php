<?php

namespace App\Http\Resources;

use App\Models\Commodity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Commodity */
class CommodityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'crop_id' => $this->crop_id,
            'name_bn' => $this->name_bn,
            'name_en' => $this->name_en,
            'category' => $this->category,
            'unit' => $this->unit,
            'icon' => $this->icon,
        ];
    }
}
