<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The app sends Accept-Language: bn or en. Error and validation messages are
 * then returned in that language (translations in lang/bn.json and
 * lang/bn/validation.php; anything missing falls back to English).
 */
class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $language = strtolower(substr((string) $request->header('Accept-Language'), 0, 2));
        app()->setLocale($language === 'bn' ? 'bn' : 'en');

        return $next($request);
    }
}
