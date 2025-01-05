<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        // Ambil user dari token JWT
        $user = JWTAuth::parseToken()->toUser();
        // Periksa apakah role cocok

        $payload = JWTAuth::parseToken()->getPayload();

        if ($payload->get('role') != $role) {
            return response()->json([
                'error' => true,
                'message' => 'Maaf, Anda tidak diizinkan mengakses.'
            ], 403);
        }

        return $next($request);
    }
}
