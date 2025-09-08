<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOtpIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Skip OTP verification for super admins
        if ($user && $user->role === 'super_admin') {
            session()->put('otp_verified', true);
        }

        // If OTP is not verified, redirect to verification form
        if (!session()->get('otp_verified')) {
            return redirect()->route('otp.verify.form')->with('error', 'Please verify your OTP.');
        }

        return $next($request);
    }
}
