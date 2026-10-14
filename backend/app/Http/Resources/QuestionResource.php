<?php

namespace App\Http\Resources;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Question */
class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isOwner = $request->user()?->id === $this->user_id;

        return [
            'id' => $this->id,
            'body' => $this->body,
            'image_url' => $this->imageUrl(),
            'status' => $this->status->value,
            'is_public' => $this->is_public,
            'is_mine' => $isOwner,
            'crop' => $this->whenLoaded('crop', fn () => $this->crop ? [
                'id' => $this->crop->id,
                'name_bn' => $this->crop->name_bn,
                'name_en' => $this->crop->name_en,
                'icon' => $this->crop->icon,
            ] : null),
            // Community Q&A shows only the district, never who asked.
            'district' => $this->whenLoaded('district', fn () => $this->district ? [
                'name_bn' => $this->district->name_bn,
                'name_en' => $this->district->name_en,
            ] : null),
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
            'answered_at' => $this->answered_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
