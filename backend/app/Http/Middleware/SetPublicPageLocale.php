<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Public web pages are in Bangla; ?lang=en (or a form field lang=en) switches to English. */
class SetPublicPageLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($request->input('lang') === 'en' ? 'en' : 'bn');

        return $next($request);
    }
}
