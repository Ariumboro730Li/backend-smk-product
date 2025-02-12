<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Constants\HttpStatusCodes;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(isset($request->auth_data)) {
            if($request->auth_data->is_administrator == true) {
                return $next($request);
            }
            return response()->json([
                'status_code'    => HttpStatusCodes::HTTP_FORBIDDEN,
                'error'     => true,
                'message'   => HttpStatusCodes::getMessageForCode(HttpStatusCodes::HTTP_FORBIDDEN)
            ], HttpStatusCodes::HTTP_FORBIDDEN);
        }
        return response()->json([
            'status_code'    => HttpStatusCodes::HTTP_FORBIDDEN,
            'error'     => true,
            'message'   => HttpStatusCodes::getMessageForCode(HttpStatusCodes::HTTP_FORBIDDEN)
        ], HttpStatusCodes::HTTP_FORBIDDEN);
    }
}
