<?php

namespace App\Http\Controllers;

use App\Models\CompanyServiceType;
use App\Models\NibOss;
use App\Models\Setting;
use Hash;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Constants\HttpStatusCodes;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    protected $companyType = [
        '01' => 'PT',
        '02' => 'CV',
        '04' => 'Badan Usaha pemerintah',
        '05' => 'Firma (Fa)',
        '06' => 'Persekutuan Perdata',
        '07' => 'Koperasi',
        '10' => 'Yayasan',
        '16' => 'Bentuk Usaha Tetap (BUT)',
        '17' => 'Perseorangan',
        '18' => 'Badan Layanan Umum (BLU)',
        '19' => 'Badan Hukum',
    ];

    public function registerManual(Request $request){
        $validator = Validator::make($request->all(), [
            "nib" => "required|unique:companies,nib,NULL,id,deleted_at,NULL",
            "name" => "required|string",
            "username" => 'required|unique:companies,username,NULL,id,deleted_at,NULL',
            "address" => "required|string",
            "phone_number" =>  [
                'required',
                'regex:/^(62|0)[0-9]{6,13}$/'
            ],
            "email" => "required|email",
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
            "service_types" => "required|array",
            "pic_name" => "required|string",
            "pic_phone" => [
                'nullable',
                'regex:/^(62|0)[0-9]{6,13}$/'
            ],
            "company_phone_number" =>  [
                'required',
                'regex:/^(62|0)[0-9]{6,13}$/'
            ],

        ], [
            'phone_number.regex' => 'Nomor Telepon harus diawali dengan 62 | 0  dan hanya terdiri dari angka.',
            'pic_phone.regex' => 'Nomor Telepon PIC harus diawali dengan 62 | 0  dan hanya terdiri dari angka.',
            'company_phone_number.regex' => 'Nomor Telepon Perusahaan harus diawali dengan 62 | 0 dan hanya terdiri dari angka.',
        ]);

        if($validator->fails()){
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => $validator->errors()->all()[0]
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        $user = new Company();
        $user->nib = strip_tags($request->nib);
        $user->username = strip_tags($request->username);
        $user->company_phone_number = strip_tags($request->company_phone_number);
        $user->phone_number = strip_tags($request->phone_number);
        $user->province_id = strip_tags($request->province_id);
        $user->city_id = strip_tags($request->city_id);
        $user->name = strip_tags($request->name);
        $user->email = strip_tags($request->email);
        $user->password = Hash::make($request->password);
        $user->pic_name = strip_tags($request->pic_name);
        $user->pic_phone = strip_tags($request->pic_phone ?? "-");
        $user->save();

        foreach ($request->service_types as $val) {
            CompanyServiceType::create([
                'company_id'        => $user->id,
                'service_type_id'   => $val,
            ]);
        }

        return response()->json([
            'status_code'   => HttpStatusCodes::HTTP_OK,
            'error'         => false,
            'message'       => 'Berhasil mendaftar.',
            'data'          => $user
        ], HttpStatusCodes::HTTP_OK);
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'nib'               => 'required|unique:companies,nib,NULL,id,deleted_at,NULL',
            'province_id'       => 'required|exists:provinces,id',
            'city_id'           => 'required|exists:cities,id',
            'service_types'     => 'required|array',
            'username'          => 'required|unique:companies,username,NULL,id,deleted_at,NULL',
            'phone_number'      => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'   => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'         => true,
                'message'       => $validator->errors()->all()[0]
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        $settingOss = Setting::where('name', 'oss')->first();

        if(!$settingOss){
            return $this->registerManual($request);
        }

        if($settingOss->value['is_active'] == false){
            return $this->registerManual($request);
        }

        $nibOss = NibOss::where('nib', $request->nib)->first();
        if ($nibOss) {
            $dataNib = $nibOss->data_nib;
        } else {
            $ossController = new OssController();
            $dataNib = $ossController->inqueryNib($request);
            if($dataNib->status() == 200){
                $dataNib = $dataNib->getData()->data;
            } else {
                return $dataNib;
            }
        }
        $companyType = $this->companyType;
        if ($companyType[$dataNib['jenis_perseroan']]) {
            $companyTypeName = $companyType[$dataNib['jenis_perseroan']];
        }
        $companyName    = $companyTypeName .' '. $dataNib['nama_perseroan'];

        $request->merge([
            'name' => $companyName,
            'email' => $dataNib['email_perusahaan'],
            'address' => $dataNib['alamat_perseroan'],
            'company_phone_number'  => $dataNib['nomor_telpon_perseroan'],
            'pic_name'   => $dataNib['penanggung_jwb'][0]['nama_penanggung_jwb'],
            'pic_phone'  => $dataNib['penanggung_jwb'][0]['no_hp_penanggung_jwb'] == "-" ? null : $dataNib['penanggung_jwb'][0]['no_hp_penanggung_jwb'],
        ]);

        return $this->registerManual($request);
    }
}
