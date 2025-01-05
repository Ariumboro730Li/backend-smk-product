<?php

namespace App\Http\Controllers;

use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Constants\HttpStatusCodes;

class UserManagementController extends Controller
{
    public function list(Request $term) {
        $validator = Validator::make($term->all(), [
            'page'      => 'required|numeric',
            'limit'     => 'required|numeric|max:50',
            'ascending' => 'required|boolean',
            'search'    => 'nullable|string',
            'id_role'   => 'nullable|exists:roles,id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => $validator->errors()->all()[0]
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }
        $query = User::select(
            'users.id',
            'users.name',
            'users.email',
            'users.username',
            'users.is_active',
            'users.nip',
            'roles.id as role_id',
            'roles.name as role_name',
            'work_units.id as work_unit_id',
            'work_units.name as work_unit_name',
            'work_units.province_id as province_id',
            'work_units.city_id as city_id',
            'users.created_at'
        )
        ->join('model_has_roles','users.id', '=', 'model_has_roles.model_id')
        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->join('work_units', 'work_units.id', 'work_unit_id');

        $query->when($term->id_role != null, function($query) use($term) {
            return $query->where('roles.id','=',$term->id_role);
        });
        $query->when($term->search != null, function ($query) use ($term) {
            return $query->where(
                function($query) use($term) {
                  return $query->where('users.email','like','%'.$term->search.'%')
                  ->orWhere('users.name','like','%'.$term->search.'%')
                  ->orWhere('users.username','like','%'.$term->search.'%');
            });
        });

        $result = $query->orderBy('users.created_at','desc')->paginate($term->limit);
        return response()->json([
            'status_code'   => HttpStatusCodes::HTTP_OK,
            'error'         => false,
            'message'       => "Successfully",
            'data'          => $result->toArray()['data'],
            'pagination'    => [
                'total'        => $result->total(),
                'count'        => $result->count(),
                'per_page'     => $result->perPage(),
                'current_page' => $result->currentPage(),
                'total_pages'  => $result->lastPage()
            ]
        ]);
    }

    public function store(Request $term) {
        $validator = Validator::make($term->all(), [
            'id_role'           => 'required|exists:roles,id',
            'name'              => 'required|string',
            'username'          => 'required|string|unique:users,username',
            'email'             => 'required|string|email|unique:users,email',
            'nip'               => 'required|string|unique:users,nip',
            // 'work_unit_id'      => 'required|exists:work_units,id',
            // 'password'          => 'required|string|max:150'
            'password' => [
                'required',
                'string',
                'min:8', // Minimal 8 karakter
                'max:150', // Maksimal 150 karakter
                'regex:/[A-Z]/', // Harus mengandung huruf besar
                'regex:/[a-z]/', // Harus mengandung huruf kecil
                'regex:/[0-9]/', // Harus mengandung angka
                'regex:/[\W]/',  // Harus mengandung simbol
            ],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => $validator->errors()->all()[0]
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }
        $timeNow = date('Y-m-d H:i:s');
        $create = User::insertGetId([
            'name'              => $term->name,
            'nip'               => $term->nip,
            'username'          => $term->username,
            'email'             => $term->email,
            'email_verified_at' => $timeNow,
            'password'          => bcrypt($term->password),
            'is_ministry'       => true,
            'is_active'         => true,
            'work_unit_id'      => 1,
            'created_at'        => $timeNow,
            'updated_at'        => $timeNow
        ]);

        DB::table('model_has_roles')->insert([
            'role_id'          => $term->id_role,
            'model_type'       => "App\Models\User",
            'model_id'         => $create
        ]);

        return response()->json([
            'status_code'   => HttpStatusCodes::HTTP_OK,
            'error'         => false,
            'message'       => "Berhasil menambahkan akun baru."
        ], HttpStatusCodes::HTTP_OK);
    }

    public function active(Request $term) {
        $validator = Validator::make($term->all(), [
            'id_user'  => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => $validator->errors()->all()[0]
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }
        $find = User::where('id','=',$term->id_user)->first();
        if($find) {
            User::where('id','=',$term->id_user)->update([
                'is_active' => true
            ]);
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_OK,
                'error'         => false,
                'message'       => "Berhasil aktifkan user."
            ], HttpStatusCodes::HTTP_OK);
        }
        return response()->json([
            'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
            'error'         => true,
            'message'       => "Data tidak ditemukan."
        ], HttpStatusCodes::HTTP_BAD_REQUEST);
    }

    public function inactive(Request $term) {
        $validator = Validator::make($term->all(), [
            'id_user' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => $validator->errors()->all()[0]
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }
        $find = User::where('id','=',$term->id_user)->first();
        if($find) {
            User::where('id','=',$term->id_user)->update([
                'is_active' => false
            ]);
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_OK,
                'error'         => false,
                'message'       => "Berhasil nonaktifkan user."
            ], HttpStatusCodes::HTTP_OK);
        }

        return response()->json([
            'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
            'error'         => true,
            'message'       => "Data tidak ditemukan."
        ], HttpStatusCodes::HTTP_BAD_REQUEST);
    }

    public function update(Request $term) {
        $validator = Validator::make($term->all(), [
            'id_user'           => 'required|exists:users,id',
            'id_role'           => 'required|exists:roles,id',
            'name'              => 'required|string',
            'username'          => 'required|string|unique:users,username,'.$term->id_user,
            'email'             => 'required|string|email|unique:users,email,'.$term->id_user,
            'nip'               => 'required|string|unique:users,nip,'.$term->id_user,
            // 'work_unit_id'      => 'required|exists:work_units,id',
            'password' => [
                'required',
                'string',
                'min:8', // Minimal 8 karakter
                'max:150', // Maksimal 150 karakter
                'regex:/[A-Z]/', // Harus mengandung huruf besar
                'regex:/[a-z]/', // Harus mengandung huruf kecil
                'regex:/[0-9]/', // Harus mengandung angka
                'regex:/[\W]/',  // Harus mengandung simbol
            ],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => $validator->errors()->all()[0]
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }
        $timeNow = date('Y-m-d H:i:s');
        if($term->password == null) {
            User::where('id','=',$term->id_user)->update([
                'name'              => $term->name,
                'nip'               => $term->nip,
                'username'          => $term->username,
                'email'             => $term->email,
                'work_unit_id'      => 1,
                'updated_at'        => $timeNow
            ]);

        } else {
            User::where('id','=',$term->id_user)->update([
                'name'              => $term->name,
                'nip'               => $term->nip,
                'username'          => $term->username,
                'email'             => $term->email,
                'password'          => bcrypt($term->password),
                'work_unit_id'      => 1,
                'updated_at'        => $timeNow
            ]);
        }

        $hasRole = DB::table('model_has_roles')->where('model_id','=',$term->id_user)->first();
        if($hasRole) {
            DB::table('model_has_roles')->where('model_id','=',$term->id_user)->delete();
        }

        DB::table('model_has_roles')->insert([
            'role_id'          => $term->id_role,
            'model_type'       => "App\Models\User",
            'model_id'         => $term->id_user
        ]);

        return response()->json([
            'status_code'   => HttpStatusCodes::HTTP_OK,
            'error'         => false,
            'message'       => 'Berhasil memperbaharui akun.'
        ], HttpStatusCodes::HTTP_OK);

    }
}
