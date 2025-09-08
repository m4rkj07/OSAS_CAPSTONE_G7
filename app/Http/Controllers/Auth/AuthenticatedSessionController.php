<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->role === 'super_admin') {
            session()->put('otp_verified', true);
            return redirect()->intended(route('redirect.after.login'));
        }

        //Apply OTP for others
        $otp = rand(100000, 999999);

        $user->otp_code = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(5);
        $user->save();

        Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otp));

        auth()->logout();
        session(['otp_user_id' => $user->id]);

        return redirect()->route('otp.verify.form')->with('status', 'OTP sent to your email.');
    }

    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $login = $request->input('login');
        $remember = $request->has('remember');

        // Determine if the login field is an email or a username
        $loginType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Attempt login using the correct field
        if (Auth::attempt([$loginType => $login, 'password' => $request->input('password')], $remember)) {
            $request->session()->regenerate();

            return $this->authenticated($request, Auth::user());
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ]);
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $request->session()->forget('otp_verified');

        return redirect('/login');
    }
}
