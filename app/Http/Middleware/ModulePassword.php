<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ModulePassword
{
    public function handle(Request $request, Closure $next, string $module)
    {
        // Just tell Blade the module requires lock initially
        $request->merge(['moduleLocked' => true]);

        return $next($request);
    }
}