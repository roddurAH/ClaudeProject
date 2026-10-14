<?php

namespace App\Services;

use App\Http\Resources\ArticleResource;
use App\Http\Resources\CropResource;
use App\Http\Resources\FertilizerResource;
use App\Models\Article;
use App\Models\Crop;
use App\Models\Fertilizer;
use Illuminate\Http\Request;

/**
 * Builds the offline knowledge-base bundle. Used by GET /api/v1/content and by
 * `php artisan krishi:export-content`, which ships a copy inside the app so it
 * works offline from the very first launch.
 */
class ContentBundle
{
    public function __construct(private readonly ContentVersion $version) {}

    public function version(): string
    {
        return $this->version->current();
    }

    public function build(Request $request): array
    {
        $crops = Crop::published()
            ->with([
                'stages',
                'problems' => fn ($q) => $q->published()->orderBy('affected_part')->orderBy('id'),
                'fertilizerDoses.fertilizer',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return [
            'version' => $this->version(),
            'generated_at' => now()->toIso8601String(),
            'crops' => CropResource::collection($crops)->resolve($request),
            'fertilizers' => FertilizerResource::collection(Fertilizer::orderBy('sort_order')->get())->resolve($request),
            'articles' => ArticleResource::collection(
                Article::published()->orderBy('sort_order')->orderByDesc('id')->get()
            )->resolve($request),
        ];
    }
}
