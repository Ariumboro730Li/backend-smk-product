<?php

namespace App\Http\Controllers;

use App\Constants\HttpStatusCodes;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input (opsional, tambahkan jika belum)
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Ambil user berdasarkan email
        $user = \App\Models\User::where('username', $request->username)->first();

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
        $credentials = $request->only('username', 'password');
        if (!auth()->attempt($credentials)) {
            return response()->json([
                'status_code'  => HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                'error'   => true,
                'message' => 'Terjadi kesalahan saat mencoba login.'
            ], HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR); // 500 untuk error server
        }

          // Buat payload untuk token
        $payload = [
            'sub' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'role' => 'internal',
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

    public function loginCompany(Request $request)
    {
        // Validasi input (opsional, tambahkan jika belum)
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Ambil user berdasarkan email
        $user = \App\Models\Company::where('username', $request->username)->first();

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
        $credentials = $request->only('username', 'password');
        if (!auth('company')->attempt($credentials)) {
            return response()->json([
                'status_code'  => HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                'error'   => true,
                'message' => 'Terjadi kesalahan saat mencoba login.'
            ], HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR); // 500 untuk error server
        }

          // Buat payload untuk token
        $payload = [
            'sub' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'role' => 'perusahaan',
            'iat' => time(), // Waktu token dibuat
        ];
        $token = $this->generateToken($payload);

        $user = auth('company')->user();
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

        if ($payload->get('role') == 'perusahaan') {
            return response()->json([
                'status_code' => HttpStatusCodes::HTTP_OK,
                'error' => true,
                'data' =>
                [
                    'user' => auth('company')->user(),
                    'payload' => [
                        'sub' => $payload->get('sub'),
                        'username' => $payload->get('username'),
                        'name' => $payload->get('name'),
                        'role' => $payload->get('role'),
                        'iat' => $payload->get('iat'),
                        'exp' => $payload->get('exp'),
                        'nbf'  => $payload->get('nbf'),
                    ]
                ]

            ], HttpStatusCodes::HTTP_OK); // 403 Forbidden
        }

        return response()->json([
            'status_code' => HttpStatusCodes::HTTP_OK,
            'error' => true,
            'data' =>
            [
                'user' => auth()->user(),
                'payload' => [
                    'sub' => $payload->get('sub'),
                    'username' => $payload->get('username'),
                    'name' => $payload->get('name'),
                    'role' => $payload->get('role'),
                    'iat' => $payload->get('iat'),
                    'exp' => $payload->get('exp'),
                    'nbf'  => $payload->get('nbf'),
                ]
        ]
    ], HttpStatusCodes::HTTP_OK); // 403 Forbidden
}

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public static function generateToken($payload)
    {
        $key = env('JWT_SECRET'); // Ambil kunci rahasia dari .env
        $ttl = env('JWT_TTL', 60); // Waktu token berlaku (dalam menit)

        // Tambahkan waktu kedaluwarsa ke payload
        $payload['exp'] = time() + ($ttl * 60); // Token berlaku selama `ttl` menit

        // Encode token JWT
        return JWT::encode($payload, $key, 'HS256');
    }
}
