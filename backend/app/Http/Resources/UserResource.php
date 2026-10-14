<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** The signed-in user's own profile. @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'role' => $this->role->value,
            'user_type' => $this->user_type,
            'district_id' => $this->district_id,
            'preferred_language' => $this->preferred_language,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
