<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Constants\HttpStatusCodes;
use Illuminate\Support\Facades\Http;

class Application
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $codeApp): Response
    {
        if($request->header('Authorization')) {
            $getMe = Http::acceptJson()
            ->withHeaders([
                'Authorization' => $request->header('Authorization')
            ])->get((string) env('AUTH_SERVICE_BASE_URL')."/auth/me/permission/application?code_application=".$codeApp."")
            ->json();
            if(isset($getMe['status_code'])) {
                if($getMe['status_code'] == HttpStatusCodes::HTTP_OK) {
                    $request->auth_token            = $request->header('Authorization');
                    $request->auth_data             = json_decode(json_encode($getMe['data']['auth']));
                    $request->auth_app_permission   = $getMe['data']['app']['permission'];
                    $request->auth_app_data         = json_decode(json_encode(array(
                        'app'           => $codeApp,
                        'type_user'     => $getMe['data']['app']['type_user'],
                        'user'          => $getMe['data']['app']['user']
                    )));
                    return $next($request);
                }
                return response()->json([
                    'status_code'       => $getMe['status_code'],
                    'error'             => true,
                    'message'           => $getMe['message']
                ], $getMe['status_code']);
            }
            return response()->json([
                'status_code'       => HttpStatusCodes::HTTP_UNAUTHORIZED,
                'error'             => true,
                'message'           => HttpStatusCodes::getMessageForCode(HttpStatusCodes::HTTP_UNAUTHORIZED)
            ], HttpStatusCodes::HTTP_UNAUTHORIZED);
        }
        return response()->json([
            'status_code'       => HttpStatusCodes::HTTP_UNAUTHORIZED,
            'error'             => true,
            'message'           => HttpStatusCodes::getMessageForCode(HttpStatusCodes::HTTP_UNAUTHORIZED)
        ], HttpStatusCodes::HTTP_UNAUTHORIZED);
        
    }
}
