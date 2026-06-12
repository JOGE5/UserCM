<?php

namespace App\Actions\Fortify;

use App\Models\TrustedDevice;
use Illuminate\Support\Facades\Cookie;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;

class RedirectIfTwoFactorAuthenticatableCustom extends RedirectIfTwoFactorAuthenticatable
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        $user = $this->validateCredentials($request);

        if ($user && optional($user)->two_factor_secret) {
            $deviceCookie = $request->cookie('trusted_device_token');

            if ($deviceCookie) {
                // Check if valid in DB
                $trusted = TrustedDevice::where('user_id', $user->id)
                    ->where('device_hash', $deviceCookie)
                    ->first();
                
                if ($trusted && $trusted->last_used_at && $trusted->last_used_at->diffInDays(now()) < 7) {
                    // Update last used at to extend the session? Or just let it expire in 7 days from creation.
                    // We let it expire based on the cookie expiration naturally.
                    return $next($request);
                }
            }
        }

        return parent::handle($request, $next);
    }
}
