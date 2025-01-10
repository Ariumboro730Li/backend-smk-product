<?php

namespace App\Http\Middleware;

use Closure;
use DB;
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

        if($role == 'internal'){
            $roleModel = DB::table('model_has_roles')->where('model_id', $user->id)
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->first();

            if($roleModel){
                $roleId = $roleModel->role_id;
                $permissionRole = DB::table('role_has_permissions')
                ->select('name', 'group', 'guard_name')
                ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                ->where('role_id', $roleId)->get()->toArray()   ;
                $request->permission = $permissionRole;
            }
        }

        return $next($request);
    }
}
