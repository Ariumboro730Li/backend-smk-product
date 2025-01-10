<?php

namespace App\Http\Controllers;

use App\Constants\HttpStatusCodes;
use App\Models\Auth\PasswordReset;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function forgot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => $validator->errors()->all()[0]
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        $token = bin2hex(random_bytes(32));
        PasswordReset::create([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);


        return response()->json([
            'status_code' => HttpStatusCodes::HTTP_OK,
            'error' => false,
            'message' => 'Berhasil mengirim email password.'
        ], HttpStatusCodes::HTTP_OK);
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|exists:password_reset_tokens,token',
            'new_password' => 'required|string',
            'confirm_password' => 'required|string|same:new_password'
        ]);

        if($validator->fails()){
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => $validator->errors()->all()[0]
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        $passwordReset = PasswordReset::where('token', $request->token)->first();
        if ($passwordReset) {
            Company::where('email', $passwordReset->email)->update([
                'password' => bcrypt($request->new_password)
            ]);
            $passwordReset->delete();
        } else {
            return response()->json([
                'status_code' => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error' => true,
                'message' => 'Token tidak valid.'
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'status_code' => HttpStatusCodes::HTTP_OK,
            'error' => false,
            'message' => 'Berhasil mengganti password.'
        ], HttpStatusCodes::HTTP_OK);
    }
}