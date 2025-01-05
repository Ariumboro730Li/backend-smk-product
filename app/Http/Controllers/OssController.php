<?php

namespace App\Http\Controllers;

use App\Constants\HttpStatusCodes;
use App\Models\Company;
use App\Models\NibOss;
use App\Models\Setting;
use App\Models\StandardIndustrialClassification;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OssController extends Controller
{
    public function inqueryNib(Request $request){
        $validator = Validator::make($request->all(), [
            "nib" => "required|string",
        ]);

        if($validator->fails()){
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => $validator->errors()->all()[0]
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        $checkNIBExist = Company::where('nib','=',$request->nib)->first();
        if($checkNIBExist) {
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => "Perusahaan telah terdaftar di sistem"
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        $setting = Setting::where('name', 'oss')->first();
        if(!$setting){
            return response()->json([
                'status_code' => HttpStatusCodes::HTTP_NOT_FOUND,
                'error' => true,
                'message' => 'Setting OSS tidak ditemukan.'
            ], HttpStatusCodes::HTTP_NOT_FOUND);
        }

        if($setting->value['is_active'] == false){
            return response()->json([
                'status_code' => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error' => true,
                'message' => 'Setting OSS tidak aktif.'
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        $username = $setting->value['username'];
        $password = $setting->value['password'];
        $url = $setting->value['url'];

        $getMe = Http::asForm()->post($url."/login",[
            "username"      => $username,
            "password"      => $password
        ])->json();

        if($getMe) {
            $token = $getMe['token'];
        } else {
            return response()->json([
                'status_code' => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error' => true,
                'message' => 'Gagal login ke OSS.'
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        if($token){
            sleep(5);

            $getMe = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$token
            ])->post($url.'/oss/inqueryNIB', [
                "INQUERYNIB" => [
                    "nib" => $request->nib
                ]
            ]);

            if(!$getMe){
                return response()->json([
                    'status_code' => HttpStatusCodes::HTTP_BAD_REQUEST,
                    'error' => true,
                    'message' => 'Gagal mengambil data dari OSS.'
                ], HttpStatusCodes::HTTP_BAD_REQUEST);
            }

            $json = $getMe->json();
            if(!$json){
                return response()->json([
                    'status_code' => HttpStatusCodes::HTTP_BAD_REQUEST,
                    'error' => true,
                    'message' => 'Gagal mengambil data dari OSS.'
                ], HttpStatusCodes::HTTP_BAD_REQUEST);
            }

            $json = $getMe->json();
            if ($json['rc'] == 200) {
                $kbliRegistered = StandardIndustrialClassification::all()->pluck('kbli');
                $dataProyekKbli = $json['data']['data_proyek'];
                $kblis = collect($dataProyekKbli)->map(function ($item)  {
                    return $item['kbli'];
                });
                $intersection = array_intersect($kblis->toArray(), $kbliRegistered->toArray());

                if (!empty($intersection)) {
                    NibOss::updateOrCreate([
                        'nib' => $request->nib
                    ], [
                        'data_nib' => $json['data']
                    ]);
                    return response()->json([
                        'status_code' => HttpStatusCodes::HTTP_OK,
                        'error' => false,
                        'data' => $json['data']
                    ], HttpStatusCodes::HTTP_OK);
                } else {
                    return response()->json([
                        'status_code' => HttpStatusCodes::HTTP_BAD_REQUEST,
                        'error' => true,
                        'message' => 'Perusahaan tidak memiliki KBLI yg sesuai.'
                    ], HttpStatusCodes::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'status_code' => HttpStatusCodes::HTTP_BAD_REQUEST,
                    'error' => true,
                    'message' => $json['message']
                ], HttpStatusCodes::HTTP_BAD_REQUEST);
            }
        }
    }
}
