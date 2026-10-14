<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes API JSON as small as possible for users on 2G/3G and metered data:
 *
 * 1. Raw UTF-8 instead of \uXXXX escapes. Every Bangla letter otherwise costs
 *    6 bytes ("ন") instead of 3.
 * 2. Gzip when the client accepts it. Text-heavy JSON shrinks ~80–90%.
 *    `php artisan serve` and a default XAMPP Apache do not compress, so we do
 *    it here; a web server that also compresses sees Content-Encoding already
 *    set and skips.
 */
class OptimizeJsonResponse
{
    private const MIN_GZIP_BYTES = 1024;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response instanceof JsonResponse) {
            return $response;
        }

        $response->setEncodingOptions($response->getEncodingOptions() | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! function_exists('gzencode')
            || $response->headers->has('Content-Encoding')
            || ! str_contains((string) $request->header('Accept-Encoding'), 'gzip')) {
            return $response;
        }

        $content = (string) $response->getContent();
        if (strlen($content) < self::MIN_GZIP_BYTES) {
            return $response;
        }

        $compressed = gzencode($content, 6);
        if ($compressed === false) {
            return $response;
        }

        // JsonResponse does not override setContent(), so this stores the raw bytes.
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Length', (string) strlen($compressed));
        $response->setVary('Accept-Encoding', false);

        return $response->setContent($compressed);
    }
}
