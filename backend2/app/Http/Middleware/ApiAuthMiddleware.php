<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check for the presence of an API token in the request header
        $token = $request->header('Authorization');
        $token='Bearer '.$request->bearerToken();
        $response=$next($request);
        $response->header('Authorization',$token);
        return $response;
        if (!$token) {
            // Token is missing, return a 401 Unauthorized response
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Your custom authentication logic goes here (e.g., validate the token)

        return $next($request);
    }
}
