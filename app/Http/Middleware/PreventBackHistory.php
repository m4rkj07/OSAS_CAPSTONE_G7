<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // If it's a PDF response, don’t apply the no-cache headers
        if ($response->headers->get('Content-Type') === 'application/pdf') {
            return $response;
        }

        return $response
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

}
