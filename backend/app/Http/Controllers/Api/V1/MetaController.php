<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ContentVersion;
use Illuminate\Http\JsonResponse;

/**
 * Small bootstrap payload the app fetches on start: settings that can change
 * without an app release, and the current knowledge-base version.
 */
class MetaController extends Controller
{
    public function __invoke(ContentVersion $version): JsonResponse
    {
        return response()->json([
            'app_name' => config('app.name'),
            'helpline_number' => config('krishi.helpline_number'),
            'min_app_version' => config('krishi.min_app_version'),
            'content_version' => $version->current(),
            'units' => config('krishi.units'),
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
