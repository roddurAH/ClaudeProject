<?php

namespace App\Http\Resources;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Alert */
class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'severity' => $this->severity->value,
            'title_bn' => $this->title_bn,
            'title_en' => $this->title_en,
            'body_bn' => $this->body_bn,
            'body_en' => $this->body_en,
            'is_national' => $this->is_national,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
        ];
    }
}
