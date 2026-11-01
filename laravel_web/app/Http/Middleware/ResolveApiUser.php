<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\DriverApiController;
use Closure;
use Illuminate\Http\Request;

class ResolveApiUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user() ?? auth('sanctum')->user() ?? DriverApiController::resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
