<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Company as TblCompany;

class CheckCompanyRegisterSpionam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spionam:check-company-register';

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
        $getData = TblCompany::where(function ($query) {
            $query->where('exist_spionam','=',null)
            ->orWhere('exist_spionam','=',false);
        })->get();
        foreach($getData as $valData) {
            $find = DB::connection('mysql_spionam')->table('dim_spionam_db_oss_nib')->where('nib','=',$valData->nib)->orderBy('tgl_pengajuan_nib','desc')->first();
            if($find) {
                $valData->exist_spionam = true;
                $valData->save();
            } else {
                $valData->exist_spionam = false;
                $valData->save();
            }
        }
    }
}
