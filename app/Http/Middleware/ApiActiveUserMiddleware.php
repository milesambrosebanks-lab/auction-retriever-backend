<?php

namespace App\Http\Middleware;

use App\Helpers\Helper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiActiveUserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return Helper::jsonErrorResponse('Unauthenticated.', 401);
        }

        if ((bool) $user->is_deleted) {
            Auth::guard('api')->logout();

            return Helper::jsonErrorResponse('Your account has been deleted.', 403);
        }

        return $next($request);
    }
}
