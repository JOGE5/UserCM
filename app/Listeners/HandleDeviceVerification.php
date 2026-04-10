<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\TrustedDevice;
use App\Mail\DeviceVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class HandleDeviceVerification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $userAgent = request()->userAgent();
        $ip = request()->ip();
        
        $deviceHash = hash('sha256', $userAgent . '|' . $ip);

        // Check if device is trusted
        $isTrusted = TrustedDevice::where('user_id', $user->id)
                                  ->where('device_hash', $deviceHash)
                                  ->exists();

        if (!$isTrusted) {
            // New device! Generate code and block session
            $code = rand(100000, 999999);
            
            Session::put('device_verification_required', true);
            Session::put('device_verification_code', $code);
            Session::put('device_verification_expires_at', now()->addMinutes(10));

            // Send Email
            Mail::to($user->email)->send(new DeviceVerificationMail($code, substr($userAgent, 0, 100)));
        } else {
            // Update last used at
            TrustedDevice::where('user_id', $user->id)
                         ->where('device_hash', $deviceHash)
                         ->update(['last_used_at' => now(), 'ip_address' => $ip]);
        }
    }
}
