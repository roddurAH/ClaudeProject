<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CropResource;
use App\Http\Resources\ProblemResource;
use App\Models\Crop;
use App\Models\Problem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CropController extends Controller
{
    /** Lightweight list. ?garden=1 → only crops suited to rooftop/homestead gardens. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $crops = Crop::published()
            ->when($request->boolean('garden'), fn ($q) => $q->where('is_garden_friendly', true))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->orderBy('sort_order')
            ->get();

        return CropResource::collection($crops);
    }

    public function show(string $slug): CropResource
    {
        $crop = Crop::published()
            ->where('slug', $slug)
            ->with(['stages', 'problems' => fn ($q) => $q->published(), 'fertilizerDoses.fertilizer'])
            ->firstOrFail();

        return new CropResource($crop);
    }

    public function problem(Problem $problem): ProblemResource
    {
        abort_unless($problem->is_published && $problem->crop->is_published, 404);

        return new ProblemResource($problem);
    }
}
