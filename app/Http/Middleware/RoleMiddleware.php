<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles)
    {
        // If not logged in, redirect to login
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Convert roles string to array
        $allowedRoles = explode(',', $roles);
        $userRole = Auth::user()->role;

        // If role does not match
        if (!in_array($userRole, $allowedRoles)) {
            Auth::logout();
            return redirect('/login')->withErrors(['access' => 'Access denied.']);
        }

        return $next($request);
    }
}
