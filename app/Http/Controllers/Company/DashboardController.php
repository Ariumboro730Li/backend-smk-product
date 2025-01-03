<?php

namespace App\Http\Controllers\Company;

use App\Constants\HttpStatusCodes;
use App\Http\Controllers\Controller;
use App\Models\Auth\Role;
use App\Models\CertificateSmk;
use App\Models\Company;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function getUserDetails(Request $request)
    {
        $authAppData = $this->getModel($request);
        $user = Company::where('id', $authAppData)->first();

        return response()->json([
            'error' => false,
            'message' => 'Data Berhasil Di Tampilkan',
            'status_code' => HttpStatusCodes::HTTP_OK,
            'data' => $user,
        ], HttpStatusCodes::HTTP_OK);
    }
    public function perusahaan(Request $request)
    {
        $Id = $this->getModel($request);
        $companyIds = Company::where('id', $Id)
            ->with([
                'province' => function ($query) {
                    $query->select('id', 'name', 'administrative_code'); // Memilih hanya kolom id dan name dari province
                },
                'city' => function ($query) {
                    $query->select('id', 'name', 'administrative_code'); // Memilih hanya kolom id dan name dari city
                }
            ])
            ->with('serviceTypes')
            ->first();


        if (!$companyIds) {
            return response()->json([
                'error' => true,
                'message' => 'Data Perusahaan Tidak Di Temukan',
                'status_code' => HttpStatusCodes::HTTP_BAD_REQUEST,
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'error' => false,
            'message' => 'Data Berhasil Di Tampilkan',
            'status_code' => HttpStatusCodes::HTTP_OK,
            'data' => $companyIds
        ]);
    }

    public function getsmk(Request $request)
    {
        $Id = $this->getModel($request);

        // Find the company by ID
        $company = Company::where('id', $Id)->first();

        // If the company is not found, return response with null values
        if (!$company) {
            return response()->json([
                'error' => false,
                'message' => 'Data Tidak Ditemukan',
                'status_code' => HttpStatusCodes::HTTP_OK,
                'data' => [
                    'id' => null,
                    'certificate_request_id' => null,
                    'certificate_file' => null,
                    'publish_date' => null,
                    'expired_date' => null,
                    'rov_file' => null,
                    'sk_file' => null,
                    'company_id' => null,
                    'is_active' => null,
                    'created_at' => null,
                    'updated_at' => null,
                    'number_of_certificate' => null,
                    'sign_by' => null,
                    'certificate_digital_url' => null,
                ],
            ], HttpStatusCodes::HTTP_OK);
        }

        // Fetch the certificate data associated with the company
        $certificate = CertificateSmk::where('company_id', $company->id)
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->first();

        // If the certificate is not found, return response with null values
        if (!$certificate) {
            return response()->json([
                'error' => false,
                'message' => 'Data Tidak Ditemukan',
                'status_code' => HttpStatusCodes::HTTP_OK,
                'data' => [
                    'id' => null,
                    'certificate_request_id' => null,
                    'certificate_file' => null,
                    'publish_date' => null,
                    'expired_date' => null,
                    'rov_file' => null,
                    'sk_file' => null,
                    'company_id' => null,
                    'is_active' => null,
                    'created_at' => null,
                    'updated_at' => null,
                    'number_of_certificate' => null,
                    'sign_by' => null,
                    'certificate_digital_url' => null,
                ],
            ], HttpStatusCodes::HTTP_OK);
        }

        // Structure the response data as per the requested format
        $responseData = [
            'id' => $certificate->id,
            'certificate_request_id' => $certificate->certificate_request_id,
            'certificate_file' => $certificate->certificate_file,
            'publish_date' => $certificate->publish_date,
            'expired_date' => $certificate->expired_date,
            'rov_file' => $certificate->rov_file,
            'sk_file' => $certificate->sk_file,
            'company_id' => $certificate->company_id,
            'is_active' => (bool) $certificate->is_active,
            'created_at' => $certificate->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $certificate->updated_at->format('Y-m-d H:i:s'),
            'number_of_certificate' => $certificate->number_of_certificate,
            'sign_by' => $certificate->sign_by,
            'certificate_digital_url' => $certificate->certificate_digital_url,
        ];

        // Return the response in JSON format with status 200
        return response()->json([
            'error' => false,
            'message' => 'Data Berhasil Di Tampilkan',
            'status_code' => HttpStatusCodes::HTTP_OK,
            'data' => $responseData,
        ], HttpStatusCodes::HTTP_OK);
    }

    public function syncOss(Request $request)
    {
        $Id = $this->getModel($request);
        // Mengambil data perusahaan berdasarkan ID
        $company = Company::find($Id);

        // Jika perusahaan tidak ditemukan, kembalikan respons dengan pesan error
        if (!$company) {
            return response()->json([
                'error' => true,
                'message' => 'Data Perusahaan Tidak Ditemukan',
                'status_code' => HttpStatusCodes::HTTP_NOT_FOUND,
            ], HttpStatusCodes::HTTP_NOT_FOUND);
        }

        // Memperbarui data perusahaan
        $company->update($request->only([
            'name',
            'username',
            'phone_number',
            'email',
            'nib',
            'address',
            'company_phone_number',
            'pic_name',
            'pic_phone',
            'established',
            'province_id',
            'city_id',
        ]));

        // Memperbarui tipe layanan jika ada
        if ($request->has('service_types')) {
            $company->serviceTypes()->sync($request->input('service_types.*.id'));
        }

        // Mengambil kembali data perusahaan yang telah diperbarui
        $company->load([
            'province' => function ($query) {
                $query->select('id', 'name', 'administrative_code');
            },
            'city' => function ($query) {
                $query->select('id', 'name', 'administrative_code');
            },
            'serviceTypes',
        ]);

        // Mempersiapkan data yang akan dikembalikan
        $responseData = [
            'id' => $company->id,
            'name' => $company->name,
            'username' => $company->username,
            'phone_number' => $company->phone_number,
            'email' => $company->email,
            'email_verified_at' => $company->email_verified_at,
            'nib' => $company->nib,
            'nib_file' => $company->nib_file ?? '-',
            'province_id' => $company->province_id,
            'city_id' => $company->city_id,
            'address' => $company->address,
            'company_phone_number' => $company->company_phone_number,
            'pic_name' => $company->pic_name,
            'pic_phone' => $company->pic_phone,
            'request_date' => $company->request_date,
            'approved_at' => $company->approved_at,
            'approved_by' => $company->approved_by,
            'is_active' => $company->is_active,
            'created_at' => $company->created_at,
            'updated_at' => $company->updated_at,
            'deleted_at' => $company->deleted_at,
            'established' => $company->established_date,
            'exist_spionam' => $company->exist_spionam,
            'province' => $company->province,
            'city' => $company->city,
            'service_types' => $company->serviceTypes,
        ];

        // Kembalikan respons sukses dengan data perusahaan
        return response()->json([
            'error' => false,
            'message' => 'Data telah berhasil disinkronkan',
            'status_code' => HttpStatusCodes::HTTP_OK,
            'data' => $responseData,
        ], HttpStatusCodes::HTTP_OK);
    }


}
