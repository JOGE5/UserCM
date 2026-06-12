<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTwoFactorEnabled
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && !$user->two_factor_secret) {
            $allowedRoutes = [
                'profile.complete.form',
                'profile.complete',
                'logout',
            ];

            if (!$request->routeIs($allowedRoutes) && !$request->is('user/two-factor*')) {
                return redirect()->route('profile.complete.form');
            }
        }

        return $next($request);
    }
}
