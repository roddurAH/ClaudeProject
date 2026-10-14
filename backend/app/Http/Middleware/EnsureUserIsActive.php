<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Blocks users an admin has disabled, even if they still hold a token/session. */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $user->currentAccessToken()?->delete();

                return response()->json(['message' => __('This account has been disabled.')], 403);
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();

            return redirect()->route('admin.login')->withErrors(['login' => 'Your account has been disabled.']);
        }

        return $next($request);
    }
}
