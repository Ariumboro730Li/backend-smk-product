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
        dd($user);
        // Periksa apakah role cocok
        if ($user->role !== $role) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized. Role tidak sesuai.'
            ], 403);
        }

        return $next($request);
    }
}
