<?php

namespace App\Http\Resources;

use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Answer */
class AnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            // Only the expert's name is shared – never their phone or email.
            'expert_name' => $this->whenLoaded('expert', fn () => $this->expert->name),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
