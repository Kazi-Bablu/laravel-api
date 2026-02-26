<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 401);
            }

        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
