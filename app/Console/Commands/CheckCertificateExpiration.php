<?php

namespace App\Console\Commands;

use App\Models\CertificateRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckCertificateExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-certificate-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $requests = CertificateRequest::whereIn('status', ['rejected', 'not_passed_assessment'])
            ->where('updated_at', '<=', Carbon::now()->subDays(13))
            ->get();

        foreach ($requests as $request) {
            $request->update(['status' => 'expired']);
        }

        return 0;
    }
}
