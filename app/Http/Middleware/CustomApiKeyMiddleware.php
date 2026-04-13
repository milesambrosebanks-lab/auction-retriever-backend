<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token || $token !== env('CUSTOM_APP_API')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
                'code' => 401,
            ], 401);
        }

        return $next($request);
    }
}
