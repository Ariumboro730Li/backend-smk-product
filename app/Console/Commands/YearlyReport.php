<?php

namespace App\Console\Commands;

use App\Jobs\YearlyReportEmail;
use Illuminate\Console\Command;

class YearlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:yearly-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To check yearly report';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dispatch(new App\Jobs\YearlyReportEmail(true));
        return Command::SUCCESS;
    }
}
