<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BridgeAuthMiddleware
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
        $bridgeSecret = config('loyalty.bridge_secret', 'default-secret');
        $providedSecret = $request->header('X-Bridge-Secret');

        if (!$providedSecret || $providedSecret !== $bridgeSecret) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid bridge secret'
            ], 401);
        }

        return $next($request);
    }
} 