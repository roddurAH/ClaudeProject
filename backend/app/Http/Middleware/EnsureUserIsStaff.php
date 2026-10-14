<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Only staff roles (not ordinary app users) may open the admin panel. */
class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isStaff(), 403, 'Admin panel access is for staff only.');

        return $next($request);
    }
}
