<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthOtpController extends Controller
{
    /**
     * Send OTP to user's email after login
     */
    public function sendOtp(User $user)
    {
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            session()->put('otp_verified', true);
            return redirect()->route('redirect.after.login');
        }

        $otp = rand(100000, 999999);

        // Save OTP and expiration
        $user->otp_code = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(5);
        $user->save();

        // Send email
        Mail::to($user->email)->send(new SendOtpMail($otp));

        // Store user ID in session temporarily
        session([
            'otp_user_id' => $user->id,
            'otp_expires_at' => $user->otp_expires_at,
        ]);

        return redirect()->route('otp.verify.form')->with('message', 'OTP has been sent to your email.');
    }

    public function resendOtp(Request $request)
    {
        $user = User::find(session('otp_user_id'));

        if (!$user) {
            return redirect()->route('login')->with('error', 'Session expired. Please log in again.');
        }

        // Cooldown check
        if (session('last_otp_sent') && now()->diffInSeconds(session('last_otp_sent')) < 60) {
            $secondsLeft = 60 - now()->diffInSeconds(session('last_otp_sent'));
            return back()->with('error', "Please wait {$secondsLeft} seconds before resending OTP.");
        }

        // Generate new OTP
        $otp = rand(100000, 999999);
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        // Send email again
        Mail::to($user->email)->send(new SendOtpMail($otp));

        // Update cooldown
        session([
            'otp_expires_at' => $user->otp_expires_at,
            'last_otp_sent' => now(),
        ]);

        return back()->with('otp_resent', 'A new OTP has been sent to your email.');

    }

    /**
     * Show OTP verification form
     */
    public function showOtpForm()
    {
        //If already verified, prevent showing the OTP form again
        if (auth()->check() && session()->get('otp_verified')) {
            return redirect()->route('redirect.after.login');
        }

        return view('auth.otp-verify');
    }

    /**
     * Handle OTP verification
     */
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp_code' => 'required']);

        $user = User::find(session('otp_user_id'));

        if (
            !$user ||
            $user->otp_code !== $request->otp_code ||
            Carbon::now()->gt($user->otp_expires_at)
        ) {
            return back()->withErrors(['otp_code' => 'Invalid OTP!']);
        }

        // Clear OTP fields
        $user->otp_verified = 1;
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        // Log the user back in
        Auth::login($user);

        session()->put('otp_verified', true);

        // Remove session data
        session()->forget(['otp_user_id', 'otp_expires_at']);

        // Redirect to intended page
        return redirect()->route('redirect.after.login');
    }

    public function cancel(Request $request)
    {
        $user = User::find(session('otp_user_id'));

        if ($user) {
            $user->update([
                'otp_code' => null,
                'otp_expires_at' => null,
            ]);
        }

        // Clear OTP-related session data
        session()->forget(['otp_user_id', 'otp_expires_at', 'otp_verified']);

        // Logout if logged in
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'OTP verification canceled. Please log in again.');
    }
}
