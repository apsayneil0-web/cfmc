<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistoryCache
{
    /**
     * Stops the browser from serving a cached copy of an authenticated page
     * (e.g. via the back button after logout) instead of re-requesting it.
     * "no-store" is what actually excludes the response from the back/forward
     * cache; "no-cache"/"must-revalidate" alone are not enough in Chrome/Firefox.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
