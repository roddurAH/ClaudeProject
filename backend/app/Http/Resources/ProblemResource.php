<?php

namespace App\Http\Resources;

use App\Models\Problem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Problem */
class ProblemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'crop_id' => $this->crop_id,
            'type' => $this->type,
            'affected_part' => $this->affected_part,
            'severity' => $this->severity,
            'name_bn' => $this->name_bn,
            'name_en' => $this->name_en,
            'image_url' => $this->imageUrl(),
            'symptoms_bn' => $this->symptoms_bn,
            'symptoms_en' => $this->symptoms_en,
            'prevention_bn' => $this->prevention_bn,
            'prevention_en' => $this->prevention_en,
            'organic_control_bn' => $this->organic_control_bn,
            'organic_control_en' => $this->organic_control_en,
            'chemical_control_bn' => $this->chemical_control_bn,
            'chemical_control_en' => $this->chemical_control_en,
        ];
    }
}
