<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceVerificationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is logged in, and verification is required, and not currently on verification routes
        if (auth()->check() && $request->session()->get('device_verification_required')) {
            if (!$request->routeIs('device.verification.form') && 
                !$request->routeIs('device.verification.verify') && 
                !$request->routeIs('device.verification.resend') &&
                !$request->routeIs('logout')) {
                
                return redirect()->route('device.verification.form');
            }
        }

        return $next($request);
    }
}
