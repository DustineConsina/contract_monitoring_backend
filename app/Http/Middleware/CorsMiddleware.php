<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get allowed origins - include all Vercel frontend variations
        $allowedOrigins = [
            'https://contract-monitoring-frontend.vercel.app',      // Current Vercel URL
            'https://contract-monitoring-frontend-b8t2.vercel.app', // Old Vercel URL
            'https://contractmonitoringbackend-production.up.railway.app',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost',
            'http://127.0.0.1',
        ];

        $origin = $request->header('origin');

        // Handle OPTIONS requests for CORS preflight
        if ($request->getMethod() === 'OPTIONS') {
            $response = new Response('', 200);
        } else {
            $response = $next($request);
        }

        // Set CORS headers if origin is allowed
        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, HEAD, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        return $response;
    }
}
