<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Constants\HttpStatusCodes;

class AppPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission, $access = null): Response
    {
        if(isset($request->auth_app_permission)) {
            $permissions = $request->auth_app_permission;
            foreach($permissions as $valPermission) {
                if($valPermission['code'] == $permission) {
                    if($access == null) {
                        return $next($request);
                    }
                    $permissionAccess = $valPermission['access'];
                    if($access == "read") {
                        if($permissionAccess['read'] == true) {
                            return $next($request);
                        }
                    } else if($access == "create") {
                        if($permissionAccess['create'] == true) {
                            return $next($request);
                        }
                    } else if($access == "update") {
                        if($permissionAccess['update'] == true) {
                            return $next($request);
                        }
                    } else if($access == "delete") {
                        if($permissionAccess['delete'] == true) {
                            return $next($request);
                        }
                    }
                }
            }
            if($access != null) {
                return response()->json([
                    'status_code'       => HttpStatusCodes::HTTP_FORBIDDEN,
                    'error'             => true,
                    'message'           => "You do not have access to => ".$permission.":".$access.""
                ], HttpStatusCodes::HTTP_FORBIDDEN);
            }
            return response()->json([
                'status_code'       => HttpStatusCodes::HTTP_FORBIDDEN,
                'error'             => true,
                'message'           => "You do not have access to => ".$permission." "
            ], HttpStatusCodes::HTTP_FORBIDDEN);
        }
        return response()->json([
            'status_code'       => HttpStatusCodes::HTTP_FORBIDDEN,
            'error'             => true,
            'message'           => HttpStatusCodes::getMessageForCode(HttpStatusCodes::HTTP_FORBIDDEN)
        ], HttpStatusCodes::HTTP_FORBIDDEN);
    }
}
