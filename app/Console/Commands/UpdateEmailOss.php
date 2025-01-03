<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Services\NibService;
use App\Services\Company\CompanyService;
use App\Services\AuthService;
use App\Jobs\NotificationEmail;
use Illuminate\Support\Str;

use App\Models\Company as TblCompany;

class UpdateEmailOss extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oss:update-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $companyType = [
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

        $getAllUser = TblCompany::all();
        foreach($getAllUser as $valUser) {
            $data    = array();
            $findNib = NibService::find($valUser->nib)['data'];

            $companyTypeName = '';
            if ($companyType[$findNib['jenis_perseroan']]) {
                $companyTypeName = $companyType[$findNib['jenis_perseroan']];
            }
    
            $companyName = $companyTypeName .' '. $findNib['nama_perseroan'];

            $data['name'] = $companyName;
            $data['email'] =  $findNib['email_perusahaan'];
            $data['address'] = $findNib['alamat_perseroan'];
            $data['company_phone_number'] = $findNib['nomor_telpon_perseroan'];
            $data['pic_name'] = $findNib['penanggung_jwb'][0]['nama_penanggung_jwb'];
            $data['pic_phone'] = $findNib['penanggung_jwb'][0]['no_hp_penanggung_jwb'];
            $data['established'] = $findNib['tgl_pengesahan_lama'] ? $findNib['tgl_pengesahan_lama'] : $findNib['tgl_pengesahan'];
            if($valUser->email != $data['email']) {
                $defaultPassword    = str_replace("-","",(string) Str::uuid());
                AuthService::forgotPassword($data['email'], true);
                $checkRequest       = AuthService::findPasswordResetByEmail($findNib['email_perusahaan']);
                $data_send_email    = array(
                    'name'                  => $data['name'],
                    'pic_name'              => $data['pic_name'],
                    'nib'                   => $valUser->nib,
                    'address'               => $data['address'],
                    'company_phone_number'  => $data['company_phone_number'],
                    'username'              => $valUser->username,
                    'phone_number'          => $valUser->phone_number,
                    'default_password'      => $defaultPassword,
                    'token'                 => $checkRequest->token,
                    'logo'                  => ""
                );
                dispatch(new NotificationEmail($data['email'], $data_send_email, 'notification-company-'.$valUser->id))->delay(Carbon::now()->addSeconds(3));
                $companyService = new CompanyService();
                $newData = $companyService->syncDataOss($valUser->id, $data);
            }
        }
    }
}
