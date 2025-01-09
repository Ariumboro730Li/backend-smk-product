<?php

namespace App\Http\Controllers;

use App\Constants\HttpStatusCodes;
use App\Models\Company;
use App\Models\ServiceType;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => $validator->errors()->all()[0]
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        if(!$request->email && !$request->username){
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => 'Email harus diisi'
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        // Ambil user berdasarkan email
        $roleUserInternal = null;
        $role = 'internal';
        $user = User::where('email', $request->email)->first();
        if(!$user){
            $role = 'internal';
            $request->merge([
                'username' => $request->email
            ]);
            $user = User::where('username', $request->email)->first();
        }
        if($user){
            $roleModel = DB::table('model_has_roles')->where('model_id', $user->id)
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->first();
            if($roleModel){
                $roleUserInternal = $roleModel->name;
            }
        }

        if(!$user){
            $role = 'company';
            $oldReq = $request->all();
            unset($oldReq['username']);
            $request = new Request($oldReq);

            $user = Company::where('email', $request->email)->first();
            if(!$user){
                $role = 'company';
                $request->merge([
                    'username' => $request->email
                ]);
                $user = Company::where('username', $request->email)->first();
            }
        }

        // Jika email tidak ditemukan, kembalikan error
        if (!$user) {
            return response()->json([
                'status_code'  => HttpStatusCodes::HTTP_NOT_FOUND,
                'error' => true,
                'message' => 'Username tidak terdaftar.'
            ], HttpStatusCodes::HTTP_NOT_FOUND); // 404 untuk not found
        }

        // Jika password tidak cocok
        if (!\Hash::check($request->password, $user->password)) {
            return response()->json([
                'status_code'  => HttpStatusCodes::HTTP_UNAUTHORIZED,
                'error' => true,
                'message' => 'Username tidak terdaftar.'
            ], HttpStatusCodes::HTTP_UNAUTHORIZED); // 401 untuk unauthorized
        }

        // Buat token JWT
        if($request->username){
            $credentials = $request->only('username', 'password');
        } else {
            $credentials = $request->only('email', 'password');
        }

        if($role == "internal"){
            if (!auth()->attempt($credentials)) {
                return response()->json([
                    'status_code'  => HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                    'error'   => true,
                    'message' => 'Terjadi kesalahan saat mencoba login.'
                ], HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR); // 500 untuk error server
            }
        } else {
            if (!auth('company')->attempt($credentials)) {
                return response()->json([
                    'status_code'  => HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                    'error'   => true,
                    'message' => 'Terjadi kesalahan saat mencoba login.'
                ], HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR); // 500 untuk error server
            }
        }

        // Buat payload untuk token
        $payload = [
            'sub' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'role' => $role,
            'internal_role' => $roleUserInternal,
            'iat' => time(), // Waktu token dibuat
        ];
        $token = $this->generateToken($payload);
        $request->merge([
            'app_user' => $user
        ]);

        // Kembalikan token jika login berhasil
        return response()->json([
            'error' => false,
            'message' => 'Login berhasil.',
            'user' => $user,
            'token' => $token
        ], 200);

    }
    public function me()
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Periksa apakah token memiliki role 'company'
        $payload = JWTAuth::parseToken()->getPayload();

        if ($payload->get('role') == 'company') {
            return response()->json([
                'status_code' => HttpStatusCodes::HTTP_OK,
                'error' => false,
                'data' =>
                [
                    'user' => auth('company')->user(),
                    'payload' => [
                        'sub' => $payload->get('sub'),
                        'username' => $payload->get('username'),
                        'name' => $payload->get('name'),
                        'role' => $payload->get('role'),
                        'internal_role' => $payload->get('internal_role'),
                        'iat' => $payload->get('iat'),
                        'exp' => $payload->get('exp'),
                        'nbf'  => $payload->get('nbf'),
                    ]
                ]

            ], HttpStatusCodes::HTTP_OK); // 403 Forbidden
        }

        return response()->json([
            'status_code' => HttpStatusCodes::HTTP_OK,
            'error' => false,
            'data' =>
            [
                'user' => auth()->user(),
                'payload' => [
                    'sub' => $payload->get('sub'),
                    'username' => $payload->get('username'),
                    'name' => $payload->get('name'),
                    'role' => $payload->get('role'),
                    'internal_role' => $payload->get('internal_role'),
                    'iat' => $payload->get('iat'),
                    'exp' => $payload->get('exp'),
                    'nbf'  => $payload->get('nbf'),
                ]
        ]
    ], HttpStatusCodes::HTTP_OK); // 403 Forbidden
}

    // public function logout()
    // {
    //     JWTAuth::invalidate(JWTAuth::getToken());
    //     return response()->json(['message' => 'Successfully logged out']);
    // }

    public static function generateToken($payload)
    {
        $key = env('JWT_SECRET'); // Ambil kunci rahasia dari .env
        $ttl = env('JWT_TTL', 60); // Waktu token berlaku (dalam menit)

        // Tambahkan waktu kedaluwarsa ke payload
        $payload['exp'] = time() + ($ttl * 60); // Token berlaku selama `ttl` menit

        // Encode token JWT
        return JWT::encode($payload, $key, 'HS256');
    }

    public function logout(Request $request)
    {
        // Ambil token dari header Authorization
        $token = JWTAuth::getToken();

        if (!$token) {
            return response()->json([
                'status_code' => 400,
                'error' => true,
                'message' => 'Token tidak ditemukan.'
            ], 400);
        }

        // Buat token tidak valid
        JWTAuth::invalidate($token);

        return response()->json([
            'status_code' => 200,
            'error' => false,
            'message' => 'Logout berhasil. Token telah dihapus.',
        ], 200);
    }

    public function serviceType(Request $request) : JsonResponse
    {
        // Validasi input request
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|max:50',
            'ascending' => 'required|boolean',
            'search' => 'nullable|string',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }


        $meta['orderBy'] = $request->ascending ? 'asc' : 'desc';
        $meta['limit'] = $request->limit;

        $query = ServiceType::orderBy('created_at', $meta['orderBy']);


        if ($request->search !== null) {
            $query->where(function($query) use ($request) {
                $columns = ['name'];
                foreach ($columns as $column) {
                    $query->orWhereRaw("LOWER({$column}) LIKE ?", ["%" . strtolower(trim($request->search)) . "%"]);
                }
            });
        }

        $data = $query->paginate($meta['limit']);

        return response()->json([
            'error' => false,
            'message' => 'Successfully',
            'status_code' => HttpStatusCodes::HTTP_OK,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'count' => $data->count(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
            ],
        ], HttpStatusCodes::HTTP_OK);
    }
}
