<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ContentBundle;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The whole offline knowledge base in one request (crops with calendars,
 * problems and fertilizer doses; fertilizers; articles).
 *
 * The app sends the ETag it already has in If-None-Match. If nothing changed
 * we answer 304 with an empty body – important on slow, metered connections.
 */
class ContentController extends Controller
{
    public function __invoke(Request $request, ContentBundle $bundle): Response
    {
        $version = $bundle->version();

        if (trim((string) $request->header('If-None-Match'), ' "') === $version) {
            return response()->noContent(304)->setEtag($version);
        }

        return response()->json($bundle->build($request))->setEtag($version);
    }
}
