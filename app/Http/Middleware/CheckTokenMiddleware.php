<?php

namespace App\Http\Middleware;

use App\Constants\HttpStatusCodes;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckTokenMiddleware
{
    public function handle($request, Closure $next)
    {

        if (!$token = JWTAuth::getToken()) {
            return response()->json([
                'status_code' => HttpStatusCodes::HTTP_UNAUTHORIZED,
                'error' => true,
                'message' => 'Token tidak ditemukan. Pastikan Anda menyertakan token di header Authorization.'
            ], HttpStatusCodes::HTTP_UNAUTHORIZED);
        }

        if (!JWTAuth::check()) {
            return response()->json([
                'status_code' => HttpStatusCodes::HTTP_UNAUTHORIZED,
                'error' => true,
                'message' => 'Token tidak valid atau telah kedaluwarsa.'
            ], HttpStatusCodes::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
