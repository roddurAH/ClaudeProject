<?php

namespace App\Http\Resources;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Article */
class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'category' => $this->category,
            'audience' => $this->audience,
            'icon' => $this->icon,
            'image_url' => $this->imageUrl(),
            'title_bn' => $this->title_bn,
            'title_en' => $this->title_en,
            'summary_bn' => $this->summary_bn,
            'summary_en' => $this->summary_en,
            'body_bn' => $this->body_bn,
            'body_en' => $this->body_en,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
