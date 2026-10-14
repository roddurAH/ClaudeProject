<?php

namespace App\Http\Resources;

use App\Models\AiMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AiMessage */
class AiMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'text' => $this->text,
            'image_url' => $this->imageUrl(),
            // null for the user's turns, and when the AI declined to answer
            'result' => $this->result,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
